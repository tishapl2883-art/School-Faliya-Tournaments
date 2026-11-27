<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ball;
use App\Models\Fixture;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManageController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', ['stats' => [
            'Registrations' => Player::count(),
            'Pending approval' => Player::where('status', 'pending')->count(),
            'Approved players' => Player::approved()->count(),
            'Players sold' => Player::where('auction_status', 'sold')->count(),
            'Teams' => Team::count().' / '.Setting::get('total_teams', 8),
            'Matches' => Fixture::count(),
        ]]);
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'brand' => 'required|string|max:100',
            'tagline' => 'required|string|max:100',
            'organizer' => 'required|string|max:100',
            'venue' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_teams' => 'required|integer|min:2|max:32',
            'registration_open' => 'required|in:0,1',
            'registration_fee' => 'required|integer|min:0',
            'upi_id' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'about' => 'nullable|string|max:3000',
            'team_purse' => 'required|integer|min:1',
            'base_price' => 'required|integer|min:1',
            'bid_increment' => 'required|integer|min:1',
            'min_squad' => 'required|integer|min:1|max:30',
            'max_squad' => 'required|integer|gte:min_squad|max:30',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'payment_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        unset($data['logo'], $data['payment_qr']);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }
        if ($request->hasFile('payment_qr')) {
            $data['payment_qr'] = $request->file('payment_qr')->store('qr', 'public');
        }
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Settings saved.');
    }

    /* ---------- Players ---------- */

    public function players(Request $request)
    {
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('q', ''));
        $players = Player::with('team')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.players', compact('players', 'status', 'search'));
    }

    public function editPlayer(Player $player)
    {
        return view('admin.player-edit', ['player' => $player, 'teams' => Team::orderBy('name')->get()]);
    }

    public function updatePlayer(Request $request, Player $player)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => ['required', 'string', 'max:15', Rule::unique('players')->ignore($player->id)],
            'village' => 'required|string|max:100',
            'role' => ['required', Rule::in(Player::ROLES)],
            'batting_style' => ['required', Rule::in(Player::BATTING)],
            'bowling_style' => ['required', Rule::in(Player::BOWLING)],
            'tshirt_size' => ['required', Rule::in(Player::SIZES)],
            'status' => 'required|in:pending,approved,rejected',
            'team_id' => 'nullable|exists:teams,id',
            'sold_price' => 'nullable|integer|min:0',
            'fantasy_credits' => 'nullable|numeric|min:5|max:15',
        ]);

        if ($player->auction_status === 'live') {
            return back()->withErrors('This player is in the auction right now. Finish that first.');
        }
        $teamId = $data['team_id'] ?? null;
        if ($teamId && $data['status'] !== 'approved') {
            return back()->withErrors('Only an approved player can be in a team.')->withInput();
        }
        if ($player->team_id != $teamId && $this->hasPlayed($player)) {
            return back()->withErrors('This player already has match records, so the team cannot be changed.')->withInput();
        }
        $data['sold_price'] = $teamId ? (int) ($data['sold_price'] ?? 0) : null;
        if ($teamId) {
            $team = Team::find($teamId);
            $others = $team->players()->whereKeyNot($player->id);
            if ((clone $others)->count() >= (int) Setting::get('max_squad', 15)) {
                return back()->withErrors('This team squad is full.')->withInput();
            }
            if ($data['sold_price'] > $team->purse - (int) $others->sum('sold_price')) {
                return back()->withErrors('This price is more than the team has left.')->withInput();
            }
        }
        $data['auction_status'] = $teamId ? 'sold' : ($player->auction_status === 'unsold' ? 'unsold' : 'pending');
        $player->update($data);

        return redirect()->route('admin.players')->with('success', 'Player updated.');
    }

    public function playerStatus(Request $request, Player $player)
    {
        $status = $request->validate(['status' => 'required|in:approved,rejected'])['status'];
        if ($status === 'rejected' && ($player->team_id || $player->auction_status === 'live')) {
            return back()->withErrors('Remove the player from the team or auction before rejecting.');
        }
        $player->update(['status' => $status]);

        return back()->with('success', "{$player->name} {$status}.");
    }

    public function deletePlayer(Player $player)
    {
        if ($this->hasPlayed($player) || $player->auction_status === 'live') {
            return back()->withErrors('This player is in the auction or has match records and cannot be deleted.');
        }
        if ($player->photo) {
            Storage::disk('public')->delete($player->photo);
        }
        if ($player->payment_proof) {
            Storage::disk('local')->delete($player->payment_proof);
        }
        $player->delete();

        return back()->with('success', 'Player deleted.');
    }

    public function paymentProof(Player $player)
    {
        abort_unless($player->payment_proof && Storage::disk('local')->exists($player->payment_proof), 404);

        return Storage::disk('local')->response($player->payment_proof);
    }

    public function exportPlayers()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads Gujarati/Hindi names correctly
            $columns = ['id', 'name', 'mobile', 'dob', 'village', 'role', 'batting_style', 'bowling_style', 'tshirt_size', 'jersey_name', 'jersey_number', 'payment_ref', 'status', 'team', 'sold_price'];
            fputcsv($out, $columns, ',', '"', '');
            foreach (Player::with('team')->orderBy('id')->cursor() as $p) {
                $row = array_map(fn ($c) => match ($c) {
                    'team' => $p->team?->name,
                    'dob' => $p->dob->format('d-m-Y'),
                    default => $p->{$c},
                }, $columns);
                // Stop spreadsheet formula injection from user-entered text.
                $row = array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v, $row);
                fputcsv($out, $row, ',', '"', '');
            }
            fclose($out);
        }, 'players.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function hasPlayed(Player $player): bool
    {
        return Ball::where('striker_id', $player->id)->orWhere('non_striker_id', $player->id)
            ->orWhere('bowler_id', $player->id)->orWhere('fielder_id', $player->id)->exists();
    }

    /* ---------- Teams ---------- */

    public function teams(Request $request)
    {
        return view('admin.teams', [
            'teams' => Team::withCount('players')->orderBy('name')->get(),
            'editing' => $request->query('edit') ? Team::find($request->query('edit')) : null,
        ]);
    }

    public function saveTeam(Request $request, ?Team $team = null)
    {
        $team ??= new Team;
        if (! $team->exists && Team::count() >= (int) Setting::get('total_teams', 8)) {
            return back()->withErrors('The maximum number of teams is already added. Change it in Settings.');
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('teams')->ignore($team->id)],
            'short_name' => 'required|string|max:6',
            'owner_name' => 'nullable|string|max:80',
            'captain_name' => 'nullable|string|max:80',
            'purse' => ['required', 'integer', 'min:'.($team->exists ? $team->spent() : 0)],
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        unset($data['logo']);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('teams', 'public');
        }
        $team->fill($data)->save();

        return redirect()->route('admin.teams')->with('success', 'Team saved.');
    }

    public function deleteTeam(Team $team)
    {
        $inMatch = Fixture::where('team_a_id', $team->id)->orWhere('team_b_id', $team->id)->exists();
        if ($inMatch || $team->players()->exists()) {
            return back()->withErrors('Remove this team\'s players and matches before deleting it.');
        }
        $team->delete();

        return back()->with('success', 'Team deleted.');
    }
}
