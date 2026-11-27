<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionState;
use App\Models\Bid;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use App\Services\AuctionRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index()
    {
        $player = AuctionState::with('player')->find(1)?->player;

        return view('admin.auction', [
            'player' => $player,
            'bids' => $player ? Bid::with('team')->where('player_id', $player->id)->orderByDesc('amount')->get() : collect(),
            'teams' => Team::orderBy('name')->get(),
            'queue' => Player::approved()->whereIn('auction_status', ['pending', 'unsold'])->orderBy('auction_status')->orderBy('name')->get(),
            'sold' => Player::with('team')->where('auction_status', 'sold')->latest('updated_at')->get(),
        ]);
    }

    /** Put a chosen player (or a random pending one) up for bidding. */
    public function start(Request $request)
    {
        return $this->locked(function (AuctionState $state) use ($request) {
            if ($state->player_id) {
                return 'Finish the current player first.';
            }
            $query = Player::approved()->whereNull('team_id');
            $player = $request->filled('player_id')
                ? $query->find($request->integer('player_id'))
                : $query->where('auction_status', 'pending')->inRandomOrder()->first();
            if (! $player) {
                return 'No player is available for auction.';
            }
            Bid::where('player_id', $player->id)->delete();
            $player->update(['auction_status' => 'live']);
            $state->update(['player_id' => $player->id]);
        });
    }

    public function bid(Request $request)
    {
        $data = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'player_id' => 'required|integer',
            'amount' => 'nullable|integer|min:1',
        ]);

        return $this->locked(function (AuctionState $state) use ($data) {
            if ((int) $state->player_id !== (int) $data['player_id']) {
                return 'The auction has moved on. Check the player and bid again.';
            }
            $cfg = array_map('intval', array_intersect_key(Setting::map(), array_flip(['base_price', 'bid_increment', 'min_squad', 'max_squad'])));
            $team = Team::find($data['team_id']);
            $top = Bid::where('player_id', $state->player_id)->orderByDesc('amount')->first();
            $current = $top ? (int) $top->amount : null;
            $amount = (int) ($data['amount'] ?? AuctionRules::nextBid($current, $cfg['base_price'], $cfg['bid_increment']));

            $error = AuctionRules::check($amount, $current, $top ? (int) $top->team_id : null, (int) $team->id, $team->remaining(), $team->players()->count(), $cfg);
            if ($error) {
                return $error;
            }
            Bid::create(['player_id' => $state->player_id, 'team_id' => $team->id, 'amount' => $amount]);
        });
    }

    public function undoBid()
    {
        return $this->locked(function (AuctionState $state) {
            Bid::where('player_id', $state->player_id)->orderByDesc('amount')->first()?->delete();
        });
    }

    public function sold(Request $request)
    {
        return $this->locked(function (AuctionState $state) use ($request) {
            if (! $state->player_id || (int) $state->player_id !== $request->integer('player_id')) {
                return 'The auction has moved on.';
            }
            $top = Bid::where('player_id', $state->player_id)->orderByDesc('amount')->first();
            if (! $top) {
                return 'There is no bid yet.';
            }
            $state->player->update(['auction_status' => 'sold', 'team_id' => $top->team_id, 'sold_price' => $top->amount]);
            $state->update(['player_id' => null]);
        }, 'Player sold.');
    }

    public function unsold(Request $request)
    {
        return $this->locked(function (AuctionState $state) use ($request) {
            if (! $state->player_id || (int) $state->player_id !== $request->integer('player_id')) {
                return 'The auction has moved on.';
            }
            Bid::where('player_id', $state->player_id)->delete();
            $state->player->update(['auction_status' => 'unsold']);
            $state->update(['player_id' => null]);
        }, 'Player marked unsold.');
    }

    /** Cancel a sale made by mistake. */
    public function revert(Player $player)
    {
        $used = DB::table('balls')->where('striker_id', $player->id)->orWhere('non_striker_id', $player->id)
            ->orWhere('bowler_id', $player->id)->orWhere('fielder_id', $player->id)->exists();
        if ($used || $player->auction_status !== 'sold') {
            return back()->withErrors('This sale cannot be cancelled.');
        }
        Bid::where('player_id', $player->id)->delete();
        $player->update(['auction_status' => 'pending', 'team_id' => null, 'sold_price' => null]);

        return back()->with('success', 'Sale cancelled.');
    }

    /** Run an action while holding the auction row, so two clicks cannot collide. */
    private function locked(callable $action, ?string $success = null)
    {
        $error = DB::transaction(fn () => $action(AuctionState::lockForUpdate()->findOrFail(1)));

        if (is_string($error)) {
            return back()->withErrors($error);
        }

        return $success ? back()->with('success', $success) : back();
    }
}
