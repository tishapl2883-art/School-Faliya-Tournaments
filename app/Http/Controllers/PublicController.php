<?php

namespace App\Http\Controllers;

use App\Models\AuctionState;
use App\Models\Bid;
use App\Models\Fixture;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use App\Services\AuctionRules;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'playerCount' => Player::where('status', '!=', 'rejected')->count(),
            'teams' => Team::orderBy('name')->get(),
            'live' => Fixture::where('status', 'live')->with('teamA', 'teamB')->get(),
            'upcoming' => Fixture::where('status', 'upcoming')->with('teamA', 'teamB')->orderBy('starts_at')->take(3)->get(),
        ]);
    }

    public function registerForm()
    {
        return view('public.register');
    }

    public function register(Request $request)
    {
        $cfg = Setting::map();
        abort_unless($cfg['registration_open'] === '1', 403, 'Registration is closed.');
        $paid = (int) $cfg['registration_fee'] > 0;

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:players,mobile'],
            'dob' => 'required|date|before:today|after:1940-01-01',
            'village' => 'required|string|max:100',
            'role' => ['required', Rule::in(Player::ROLES)],
            'batting_style' => ['required', Rule::in(Player::BATTING)],
            'bowling_style' => ['required', Rule::in(Player::BOWLING)],
            'tshirt_size' => ['required', Rule::in(Player::SIZES)],
            'jersey_name' => 'nullable|string|max:20',
            'jersey_number' => 'nullable|integer|min:0|max:999',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'payment_ref' => [$paid ? 'required' : 'nullable', 'string', 'max:60'],
            'payment_proof' => [$paid ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'terms' => 'accepted',
        ], [
            'mobile.regex' => 'Enter a valid 10 digit mobile number.',
            'mobile.unique' => 'This mobile number is already registered.',
        ]);
        unset($data['terms']);

        $data['photo'] = $request->file('photo')->store('players', 'public');
        // Payment screenshots stay on the private disk; only the admin can open them.
        $data['payment_proof'] = $request->file('payment_proof')?->store('payments', 'local');

        $player = Player::create($data);

        return redirect()->route('register')->with('success', "Registration received. Your registration number is #{$player->id}. It will appear after the organiser approves it.");
    }

    public function players(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');
        $players = Player::approved()->with('team')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('village', 'like', "%{$search}%")))
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->orderBy('name')->paginate(24)->withQueryString();

        return view('public.players', compact('players', 'search', 'role'));
    }

    public function teams()
    {
        return view('public.teams', ['teams' => Team::withCount('players')->orderBy('name')->get()]);
    }

    public function team(Team $team)
    {
        return view('public.team', ['team' => $team, 'players' => $team->players()->approved()->orderBy('name')->get()]);
    }

    public function matches()
    {
        $all = Fixture::with('teamA', 'teamB')->orderBy('starts_at')->get();

        return view('public.matches', ['groups' => [
            'Live' => $all->where('status', 'live'),
            'Upcoming' => $all->where('status', 'upcoming'),
            'Results' => $all->where('status', 'completed')->reverse(),
        ]]);
    }

    public function match(Fixture $fixture)
    {
        return view('public.match', MatchService::card($fixture));
    }

    public function matchLive(Fixture $fixture)
    {
        return view('partials.scorecard', MatchService::card($fixture));
    }

    public function live()
    {
        return view('public.live', ['cards' => Fixture::where('status', 'live')->get()->map(fn ($f) => MatchService::card($f))]);
    }

    public function liveData()
    {
        return view('partials.live-list', ['cards' => Fixture::where('status', 'live')->get()->map(fn ($f) => MatchService::card($f))]);
    }

    public function points()
    {
        return view('public.points', ['rows' => MatchService::pointsTable(), 'leaders' => MatchService::leaders()]);
    }

    public function auction()
    {
        return view('public.auction', $this->auctionData());
    }

    public function auctionLive()
    {
        return view('partials.auction-board', $this->auctionData());
    }

    public function page(string $page)
    {
        return view('public.page', compact('page'));
    }

    private function auctionData(): array
    {
        $cfg = Setting::map();
        $player = AuctionState::with('player')->find(1)?->player;
        $top = $player ? Bid::with('team')->where('player_id', $player->id)->orderByDesc('amount')->first() : null;
        $teams = Team::withCount('players')->withSum('players', 'sold_price')->orderBy('name')->get()
            ->each(function ($t) use ($cfg) {
                $t->balance = $t->purse - (int) $t->players_sum_sold_price;
                $t->max_bid = AuctionRules::maxBid($t->balance, $t->players_count, (int) $cfg['min_squad'], (int) $cfg['base_price']);
            });

        return [
            'player' => $player,
            'top' => $top,
            'teams' => $teams,
            'recent' => Player::with('team')->where('auction_status', 'sold')->latest('updated_at')->take(8)->get(),
            'counts' => Player::approved()->selectRaw('auction_status, count(*) as c')->groupBy('auction_status')->pluck('c', 'auction_status'),
        ];
    }
}
