<?php

namespace App\Http\Controllers;

use App\Models\FantasyEntry;
use App\Models\Fixture;
use App\Services\FantasyPointsCalculator;
use App\Services\FantasyRules;
use App\Services\FantasyService;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FantasyController extends Controller
{
    public function index()
    {
        $fixtures = Fixture::with('teamA', 'teamB')
            ->withCount('fantasyEntries')
            ->orderBy('starts_at')
            ->get();

        $groups = [
            'Live' => $fixtures->where('status', 'live'),
            'Upcoming' => $fixtures->where('status', 'upcoming'),
            'Completed' => $fixtures->where('status', 'completed')->reverse(),
        ];

        $myMobile = session('cric11_mobile');
        $myEntries = $myMobile
            ? FantasyEntry::where('participant_mobile', $myMobile)
                ->with('fixture.teamA', 'fixture.teamB', 'captain', 'viceCaptain')
                ->latest()->get()
            : collect();

        $overall = FantasyService::overallLeaderboard();

        return view('cric11.index', compact('groups', 'myEntries', 'myMobile', 'overall'));
    }

    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'participant_mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'participant_name' => ['nullable', 'string', 'max:80'],
        ], [
            'participant_mobile.regex' => 'Enter a valid 10 digit mobile number.',
        ]);

        session(['cric11_mobile' => $data['participant_mobile']]);
        if (! empty($data['participant_name'])) {
            session(['cric11_name' => trim($data['participant_name'])]);
        }

        return back()->with('success', 'Showing Cric11 teams for '.$data['participant_mobile'].'.');
    }

    public function match(Fixture $fixture)
    {
        return view('cric11.match', $this->matchPayload($fixture));
    }

    public function matchData(Fixture $fixture)
    {
        return view('cric11.partials.match-live', $this->matchPayload($fixture));
    }

    public function create(Fixture $fixture)
    {
        abort_if($fixture->status !== 'upcoming', 403, 'Teams are locked because this match has already started.');
        $fixture->load('teamA', 'teamB');
        $pool = FantasyService::pool($fixture);
        $limits = FantasyRules::limits(FantasyService::poolArray($pool));

        $mobile = session('cric11_mobile', '');
        $existingCount = $mobile
            ? $fixture->fantasyEntries()->where('participant_mobile', $mobile)->count()
            : 0;
        $defaultTeamName = trim((string) session('cric11_name', 'Team'))
            ? trim((string) session('cric11_name', 'My')).' XI #'.($existingCount + 1)
            : 'My XI #'.($existingCount + 1);

        return view('cric11.builder', [
            'fixture' => $fixture,
            'pool' => $pool,
            'limits' => $limits,
            'entry' => null,
            'selectedIds' => old('players', []),
            'captainId' => (int) old('captain_id', 0),
            'viceCaptainId' => (int) old('vice_captain_id', 0),
            'defaultTeamName' => $defaultTeamName,
        ]);
    }

    public function store(Request $request, Fixture $fixture)
    {
        abort_if($fixture->status !== 'upcoming', 403, 'Teams are locked because this match has already started.');

        $data = $request->validate([
            'participant_name' => 'required|string|max:80',
            'participant_mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'team_name' => 'required|string|max:60',
            'players' => 'required|array',
            'players.*' => 'integer',
            'captain_id' => 'required|integer',
            'vice_captain_id' => 'required|integer',
        ], [
            'participant_mobile.regex' => 'Enter a valid 10 digit mobile number.',
        ]);

        $count = $fixture->fantasyEntries()->where('participant_mobile', $data['participant_mobile'])->count();
        if ($count >= FantasyRules::MAX_ENTRIES_PER_USER) {
            return back()->withErrors('You can join with at most '.FantasyRules::MAX_ENTRIES_PER_USER.' teams per match.')->withInput();
        }

        $pool = FantasyService::pool($fixture);
        $poolArray = FantasyService::poolArray($pool);
        $selectedIds = array_values(array_unique(array_map('intval', $data['players'])));

        if ($err = FantasyRules::validateSelection($poolArray, $selectedIds, (int) $data['captain_id'], (int) $data['vice_captain_id'])) {
            return back()->withErrors($err)->withInput();
        }

        $totalCredits = round($pool->whereIn('id', $selectedIds)->sum(fn ($p) => $p->fantasyCredits()), 1);
        $token = Str::random(40);

        $entry = DB::transaction(function () use ($fixture, $data, $selectedIds, $totalCredits, $token) {
            $entry = FantasyEntry::create([
                'fixture_id' => $fixture->id,
                'participant_name' => trim($data['participant_name']),
                'participant_mobile' => $data['participant_mobile'],
                'team_name' => trim($data['team_name']),
                'captain_id' => (int) $data['captain_id'],
                'vice_captain_id' => (int) $data['vice_captain_id'],
                'total_credits' => $totalCredits,
                'edit_token' => $token,
            ]);
            $entry->players()->sync($selectedIds);

            return $entry;
        });

        $tokens = session('cric11_tokens', []);
        $tokens[$entry->id] = $token;
        session([
            'cric11_tokens' => $tokens,
            'cric11_name' => $entry->participant_name,
            'cric11_mobile' => $entry->participant_mobile,
        ]);

        return redirect()->route('cric11.match', $fixture)->with('success', "Cric11 team \"{$entry->team_name}\" joined!");
    }

    public function edit(FantasyEntry $entry)
    {
        $fixture = $entry->fixture->load('teamA', 'teamB');
        abort_if($fixture->status !== 'upcoming', 403, 'Match has started; teams are now locked.');
        abort_unless($this->canManage($entry), 403, 'You can only edit teams created from this browser.');

        $pool = FantasyService::pool($fixture);
        $limits = FantasyRules::limits(FantasyService::poolArray($pool));

        return view('cric11.builder', [
            'fixture' => $fixture,
            'pool' => $pool,
            'limits' => $limits,
            'entry' => $entry,
            'selectedIds' => old('players', $entry->players()->pluck('players.id')->all()),
            'captainId' => (int) old('captain_id', $entry->captain_id),
            'viceCaptainId' => (int) old('vice_captain_id', $entry->vice_captain_id),
            'defaultTeamName' => $entry->team_name,
        ]);
    }

    public function update(Request $request, FantasyEntry $entry)
    {
        $fixture = $entry->fixture;
        abort_if($fixture->status !== 'upcoming', 403, 'Match has started; teams are now locked.');
        abort_unless($this->canManage($entry), 403, 'You can only edit teams created from this browser.');

        $data = $request->validate([
            'participant_name' => 'required|string|max:80',
            'team_name' => 'required|string|max:60',
            'players' => 'required|array',
            'players.*' => 'integer',
            'captain_id' => 'required|integer',
            'vice_captain_id' => 'required|integer',
        ]);

        $pool = FantasyService::pool($fixture);
        $poolArray = FantasyService::poolArray($pool);
        $selectedIds = array_values(array_unique(array_map('intval', $data['players'])));

        if ($err = FantasyRules::validateSelection($poolArray, $selectedIds, (int) $data['captain_id'], (int) $data['vice_captain_id'])) {
            return back()->withErrors($err)->withInput();
        }

        $totalCredits = round($pool->whereIn('id', $selectedIds)->sum(fn ($p) => $p->fantasyCredits()), 1);

        DB::transaction(function () use ($entry, $data, $selectedIds, $totalCredits) {
            $entry->update([
                'participant_name' => trim($data['participant_name']),
                'team_name' => trim($data['team_name']),
                'captain_id' => (int) $data['captain_id'],
                'vice_captain_id' => (int) $data['vice_captain_id'],
                'total_credits' => $totalCredits,
            ]);
            $entry->players()->sync($selectedIds);
        });

        session(['cric11_name' => $entry->participant_name]);

        return redirect()->route('cric11.match', $fixture)->with('success', "Cric11 team \"{$entry->team_name}\" updated.");
    }

    public function entry(FantasyEntry $entry)
    {
        $fixture = $entry->fixture->load('teamA', 'teamB');
        $canView = $fixture->status !== 'upcoming' || $this->canManage($entry);
        abort_unless($canView, 403, 'Other teams become visible once the match goes live.');

        $entry->load('players.team', 'captain', 'viceCaptain');
        $pool = FantasyService::pool($fixture);
        $pointsById = FantasyService::matchPoints($fixture, $pool);
        $totalPoints = FantasyPointsCalculator::entryScore(
            $pointsById,
            $entry->players->pluck('id')->map(fn ($id) => (int) $id)->all(),
            (int) $entry->captain_id,
            (int) $entry->vice_captain_id
        );

        $byRole = ['WK' => collect(), 'BAT' => collect(), 'AR' => collect(), 'BOWL' => collect()];
        foreach ($entry->players as $p) {
            $byRole[$p->roleCode()]->push($p);
        }

        return view('cric11.entry', [
            'fixture' => $fixture,
            'entry' => $entry,
            'pointsById' => $pointsById,
            'totalPoints' => $totalPoints,
            'byRole' => $byRole,
            'canEdit' => $fixture->status === 'upcoming' && $this->canManage($entry),
        ]);
    }

    private function canManage(FantasyEntry $entry): bool
    {
        if (Auth::check()) {
            return true;
        }
        $tokens = session('cric11_tokens', []);

        return isset($tokens[$entry->id]) && hash_equals((string) $entry->edit_token, (string) $tokens[$entry->id]);
    }

    private function matchPayload(Fixture $fixture): array
    {
        $fixture->load('teamA', 'teamB', 'innings.battingTeam');
        $pool = FantasyService::pool($fixture);
        $pointsById = FantasyService::matchPoints($fixture, $pool);
        $leaderboard = FantasyService::leaderboard($fixture, $pool, $pointsById);
        $dreamTeam = $fixture->status !== 'upcoming'
            ? FantasyPointsCalculator::dreamTeam(FantasyService::poolArray($pool), $pointsById)
            : null;

        $playerStats = $pool->map(function ($p) use ($pointsById) {
            $pt = $pointsById[$p->id] ?? ['playing' => 0, 'batting' => 0, 'bowling' => 0, 'fielding' => 0, 'total' => 0, 'stats' => null];

            return [
                'player' => $p,
                'points' => $pt,
            ];
        })->sortByDesc(fn ($r) => $r['points']['total'])->values();

        $card = MatchService::card($fixture);
        $tokens = session('cric11_tokens', []);
        $myMobile = session('cric11_mobile');

        return [
            'fixture' => $fixture,
            'pool' => $pool,
            'pointsById' => $pointsById,
            'leaderboard' => $leaderboard,
            'dreamTeam' => $dreamTeam,
            'playerStats' => $playerStats,
            'card' => $card,
            'ownedTokens' => $tokens,
            'myMobile' => $myMobile,
            'rules' => FantasyPointsCalculator::RULES,
        ];
    }
}
