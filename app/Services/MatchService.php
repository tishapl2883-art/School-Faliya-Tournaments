<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Inning;
use App\Models\Player;
use App\Models\Team;

/** Connects the database to the pure scoring classes. */
class MatchService
{
    public static function score(Inning $inning): array
    {
        $balls = $inning->balls()->get()->map(fn ($b) => [
            'striker_id' => (int) $b->striker_id,
            'non_striker_id' => (int) $b->non_striker_id,
            'bowler_id' => (int) $b->bowler_id,
            'runs_bat' => (int) $b->runs_bat,
            'extra_type' => $b->extra_type,
            'extra_runs' => (int) $b->extra_runs,
            'wicket_type' => $b->wicket_type,
            'out_player_id' => $b->out_player_id ? (int) $b->out_player_id : null,
            'fielder_id' => $b->fielder_id ? (int) $b->fielder_id : null,
        ])->all();

        return ScoreCalculator::calculate($balls);
    }

    /** Wickets that end an innings: limited by players per side and by squad size. */
    public static function wicketLimit(Fixture $fixture, int $teamId): int
    {
        $squad = Player::approved()->where('team_id', $teamId)->count();

        return max(1, min($fixture->players_per_side, $squad) - 1);
    }

    /** Everything a scorecard view needs. */
    public static function card(Fixture $fixture): array
    {
        $fixture->load('teamA', 'teamB', 'tossWinner', 'innings.battingTeam', 'innings.bowlingTeam');
        $innings = $fixture->innings->map(fn ($i) => ['model' => $i, 'score' => self::score($i)])->all();
        $names = Player::whereIn('team_id', [$fixture->team_a_id, $fixture->team_b_id])->pluck('name', 'id')->all();

        $target = null;
        $chase = null;
        if (count($innings) === 2) {
            $target = $innings[0]['score']['runs'] + 1;
            if ($fixture->status === 'live') {
                $need = max(0, $target - $innings[1]['score']['runs']);
                $left = max(0, $fixture->overs * 6 - $innings[1]['score']['legal_balls']);
                $chase = $innings[1]['model']->battingTeam->name." need {$need} runs in {$left} balls";
            }
        }

        return compact('fixture', 'innings', 'names', 'target', 'chase');
    }

    /** Mark an innings complete when its limit is reached, and finish the match after the second. */
    public static function autoClose(Inning $inning): void
    {
        $fixture = $inning->fixture;
        $score = self::score($inning);
        $done = $score['legal_balls'] >= $fixture->overs * 6
            || $score['wickets'] >= self::wicketLimit($fixture, $inning->batting_team_id);

        if ($inning->number === 2) {
            $first = $fixture->innings()->where('number', 1)->first();
            $done = $done || $score['runs'] > self::score($first)['runs'];
        }
        if ($done) {
            self::closeInning($inning);
        }
    }

    public static function closeInning(Inning $inning): void
    {
        $inning->update(['completed' => true]);
        if ($inning->number !== 2) {
            return;
        }
        $fixture = $inning->fixture;
        $first = $fixture->innings()->where('number', 1)->first();
        $a = self::score($first)['runs'];
        $score = self::score($inning);
        $b = $score['runs'];

        if ($b > $a) {
            $wkts = self::wicketLimit($fixture, $inning->batting_team_id) - $score['wickets'];
            $winner = $inning->batting_team_id;
            $text = Team::find($winner)->name." won by {$wkts} wicket".($wkts === 1 ? '' : 's');
        } elseif ($a > $b) {
            $winner = $first->batting_team_id;
            $runs = $a - $b;
            $text = Team::find($winner)->name." won by {$runs} run".($runs === 1 ? '' : 's');
        } else {
            $winner = null;
            $text = 'Match tied';
        }

        $fixture->update([
            'status' => 'completed',
            'winner_id' => $winner,
            'result_type' => $winner ? 'win' : 'tie',
            'result_text' => $text,
        ]);
    }

    public static function pointsTable(): array
    {
        $teams = Team::orderBy('name')->get()->keyBy('id');
        $matches = Fixture::where('stage', 'League')->where('status', 'completed')->with('innings')->get()
            ->map(fn ($f) => [
                'team_a' => $f->team_a_id,
                'team_b' => $f->team_b_id,
                'result_type' => $f->result_type,
                'winner' => $f->winner_id,
                'innings' => $f->innings->map(function ($i) use ($f) {
                    $s = self::score($i);

                    return [
                        'team' => $i->batting_team_id, 'runs' => $s['runs'], 'balls' => $s['legal_balls'],
                        'all_out' => $s['wickets'] >= self::wicketLimit($f, $i->batting_team_id),
                        'quota_balls' => $f->overs * 6,
                    ];
                })->all(),
            ])->all();

        return array_map(
            fn ($row) => $row + ['team' => $teams[$row['team_id']]],
            PointsTable::build($teams->keys()->all(), $matches)
        );
    }

    /** Top run scorers and wicket takers across the tournament. */
    public static function leaders(): array
    {
        $runs = [];
        $wickets = [];
        foreach (Inning::all() as $inning) {
            $s = self::score($inning);
            foreach ($s['batters'] as $id => $b) {
                $runs[$id] = ($runs[$id] ?? 0) + $b['runs'];
            }
            foreach ($s['bowlers'] as $id => $b) {
                $wickets[$id] = ($wickets[$id] ?? 0) + $b['wickets'];
            }
        }
        $top = function (array $totals) {
            arsort($totals);
            $totals = array_slice(array_filter($totals), 0, 10, true);
            $players = Player::with('team')->findMany(array_keys($totals))->keyBy('id');
            $list = [];
            foreach ($totals as $id => $value) {
                $list[] = ['player' => $players[$id], 'value' => $value];
            }

            return $list;
        };

        return ['runs' => $top($runs), 'wickets' => $top($wickets)];
    }
}
