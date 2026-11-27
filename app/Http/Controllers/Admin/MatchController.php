<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ball;
use App\Models\Fixture;
use App\Models\Inning;
use App\Models\Player;
use App\Models\Team;
use App\Services\MatchService;
use App\Services\ScoreCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.matches', [
            'matches' => Fixture::with('teamA', 'teamB')->orderBy('starts_at')->get(),
            'teams' => Team::orderBy('name')->get(),
            'editing' => $request->query('edit') ? Fixture::find($request->query('edit')) : null,
            'nextNo' => (int) Fixture::max('match_no') + 1,
        ]);
    }

    public function save(Request $request, ?Fixture $fixture = null)
    {
        $fixture ??= new Fixture;
        if ($fixture->exists && $fixture->status !== 'upcoming') {
            return back()->withErrors('A match that has started cannot be edited.');
        }
        $data = $request->validate([
            'match_no' => 'required|integer|min:1|max:999',
            'stage' => ['required', Rule::in(Fixture::STAGES)],
            'team_a_id' => 'required|exists:teams,id',
            'team_b_id' => 'required|exists:teams,id|different:team_a_id',
            'starts_at' => 'required|date',
            'overs' => 'required|integer|min:1|max:50',
            'players_per_side' => 'required|integer|min:2|max:11',
        ]);
        $fixture->fill($data)->save();

        return redirect()->route('admin.matches')->with('success', 'Match saved.');
    }

    public function delete(Fixture $fixture)
    {
        $fixture->delete();

        return redirect()->route('admin.matches')->with('success', 'Match deleted.');
    }

    public function score(Fixture $fixture)
    {
        $card = MatchService::card($fixture);
        $current = collect($card['innings'])->first(fn ($i) => ! $i['model']->completed);
        $batting = $bowling = collect();
        if ($current) {
            $batting = $this->squad($current['model']->batting_team_id)->whereNotIn('id', $current['score']['out'])->values();
            $bowling = $this->squad($current['model']->bowling_team_id);
        }

        return view('admin.score', $card + compact('current', 'batting', 'bowling'));
    }

    /** Record the toss and open the first innings. */
    public function toss(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'toss_winner_id' => ['required', Rule::in([$fixture->team_a_id, $fixture->team_b_id])],
            'toss_decision' => 'required|in:bat,bowl',
        ]);
        if ($fixture->status !== 'upcoming') {
            return back()->withErrors('This match has already started.');
        }
        foreach ([$fixture->teamA, $fixture->teamB] as $team) {
            if ($this->squad($team->id)->count() < 2) {
                return back()->withErrors("{$team->name} needs at least 2 players in its squad.");
            }
        }
        $winner = (int) $data['toss_winner_id'];
        $other = $winner === (int) $fixture->team_a_id ? (int) $fixture->team_b_id : (int) $fixture->team_a_id;
        [$bat, $bowl] = $data['toss_decision'] === 'bat' ? [$winner, $other] : [$other, $winner];

        DB::transaction(function () use ($fixture, $data, $bat, $bowl) {
            $fixture->update($data + ['status' => 'live']);
            $fixture->innings()->create(['number' => 1, 'batting_team_id' => $bat, 'bowling_team_id' => $bowl]);
        });

        return back()->with('success', 'Match started.');
    }

    public function ball(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'inning_id' => 'required|integer',
            'ball_count' => 'required|integer',
            'striker_id' => 'required|integer|different:non_striker_id',
            'non_striker_id' => 'required|integer',
            'bowler_id' => 'required|integer',
            'runs' => 'required|integer|min:0|max:7',
            'extra_type' => ['nullable', Rule::in(ScoreCalculator::EXTRAS)],
            'wicket_type' => ['nullable', Rule::in(array_keys(ScoreCalculator::WICKETS))],
            'out_who' => 'nullable|in:striker,non_striker',
            'fielder_id' => 'nullable|integer',
        ], ['striker_id.different' => 'Striker and non striker must be different players.']);

        $error = DB::transaction(function () use ($data, $fixture) {
            $inning = Inning::lockForUpdate()->where('fixture_id', $fixture->id)->find($data['inning_id']);
            if (! $inning || $inning->completed || $fixture->status !== 'live') {
                return 'This innings is not open for scoring.';
            }
            // Blocks double taps and a second scorer working from an old screen.
            if ($inning->balls()->count() !== (int) $data['ball_count']) {
                return 'The score changed. Check it and enter the ball again.';
            }
            $score = MatchService::score($inning);
            $batting = $this->squad($inning->batting_team_id)->pluck('id')->all();
            $bowling = $this->squad($inning->bowling_team_id)->pluck('id')->all();

            foreach (['striker_id', 'non_striker_id'] as $key) {
                if (! in_array((int) $data[$key], $batting) || in_array((int) $data[$key], $score['out'])) {
                    return 'Choose batters who are in the batting team and not out.';
                }
            }
            $bowler = (int) $data['bowler_id'];
            if (! in_array($bowler, $bowling)) {
                return 'Choose a bowler from the bowling team.';
            }
            if ($score['bowler_id'] === null && $score['previous_over_bowler_id'] === $bowler) {
                return 'A bowler cannot bowl two overs in a row.';
            }

            $extra = $data['extra_type'] ?? null;
            $wicket = $data['wicket_type'] ?? null;
            $runs = (int) $data['runs'];
            $error = ScoreCalculator::validateDelivery($extra, $runs, $wicket, $score['free_hit']);
            if ($error) {
                return $error;
            }
            $outId = null;
            if ($wicket) {
                // Only a run out can dismiss the non striker.
                $outId = $wicket === 'run_out' && ($data['out_who'] ?? '') === 'non_striker' ? $data['non_striker_id'] : $data['striker_id'];
            }
            $fielder = in_array($wicket, ['caught', 'run_out', 'stumped']) ? ($data['fielder_id'] ?? null) : null;
            if ($fielder && ! in_array((int) $fielder, $bowling)) {
                return 'The fielder must be from the bowling team.';
            }

            Ball::create(ScoreCalculator::split($extra, $runs) + [
                'inning_id' => $inning->id,
                'striker_id' => $data['striker_id'],
                'non_striker_id' => $data['non_striker_id'],
                'bowler_id' => $bowler,
                'extra_type' => $extra,
                'wicket_type' => $wicket,
                'out_player_id' => $outId,
                'fielder_id' => $fielder,
            ]);
            MatchService::autoClose($inning);
        });

        return is_string($error) ? back()->withErrors($error) : back();
    }

    /** Remove the last ball. Re-opens the innings or match if that ball had closed it. */
    public function undo(Fixture $fixture)
    {
        DB::transaction(function () use ($fixture) {
            $inning = $fixture->innings()->get()->last();
            if (! $inning) {
                return;
            }
            $last = Ball::where('inning_id', $inning->id)->orderByDesc('id')->first();
            if (! $last) {
                // An empty second innings: remove it and go back to the first.
                if ($inning->number === 2) {
                    $inning->delete();
                    $inning = $fixture->innings()->get()->last();
                    $last = Ball::where('inning_id', $inning->id)->orderByDesc('id')->first();
                }
            }
            $last?->delete();
            $inning->update(['completed' => false]);
            $fixture->update(['status' => 'live', 'winner_id' => null, 'result_type' => null, 'result_text' => null]);
        });

        return back()->with('success', 'Last ball removed.');
    }

    public function endInning(Fixture $fixture)
    {
        $inning = $fixture->innings()->where('completed', false)->first();
        if ($inning) {
            MatchService::closeInning($inning);
        }

        return back()->with('success', 'Innings ended.');
    }

    public function secondInning(Fixture $fixture)
    {
        $all = $fixture->innings()->get();
        if ($fixture->status !== 'live' || $all->count() !== 1 || ! $all[0]->completed) {
            return back()->withErrors('The second innings cannot start yet.');
        }
        $fixture->innings()->create([
            'number' => 2,
            'batting_team_id' => $all[0]->bowling_team_id,
            'bowling_team_id' => $all[0]->batting_team_id,
        ]);

        return back()->with('success', 'Second innings started.');
    }

    /** Manual result: rain, walkover, super over winner and so on. */
    public function result(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'result_type' => 'required|in:win,tie,no_result',
            'winner_id' => ['nullable', 'required_if:result_type,win', Rule::in([$fixture->team_a_id, $fixture->team_b_id])],
            'result_text' => 'required|string|max:150',
        ]);
        if ($data['result_type'] !== 'win') {
            $data['winner_id'] = null;
        }
        $fixture->innings()->update(['completed' => true]);
        $fixture->update($data + ['status' => 'completed']);

        return back()->with('success', 'Result saved.');
    }

    private function squad(int $teamId)
    {
        return Player::approved()->where('team_id', $teamId)->orderBy('name')->get();
    }
}
