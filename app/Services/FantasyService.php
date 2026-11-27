<?php

namespace App\Services;

use App\Models\FantasyEntry;
use App\Models\Fixture;
use App\Models\Player;
use Illuminate\Support\Collection;

/** Bridges database models with pure FantasyRules and FantasyPointsCalculator. */
class FantasyService
{
    /** Approved players from both teams in a fixture, enriched with credits and role_code. */
    public static function pool(Fixture $fixture): Collection
    {
        return Player::approved()
            ->whereIn('team_id', [$fixture->team_a_id, $fixture->team_b_id])
            ->with('team')
            ->orderBy('name')
            ->get()
            ->map(function (Player $p) {
                $p->credits = $p->fantasyCredits();
                $p->role_code = $p->roleCode();

                return $p;
            });
    }

    public static function poolArray(Collection $players): array
    {
        return $players->map(fn (Player $p) => [
            'id' => (int) $p->id,
            'team_id' => (int) $p->team_id,
            'role_code' => $p->roleCode(),
            'credits' => $p->fantasyCredits(),
        ])->values()->all();
    }

    /** Compute live fantasy points for every player involved in a fixture. */
    public static function matchPoints(Fixture $fixture, ?Collection $pool = null): array
    {
        $pool ??= self::pool($fixture);
        $rolesById = $pool->pluck('role', 'id')->all();

        $fixture->loadMissing('innings.balls');
        $inningsBalls = $fixture->innings->map(fn ($inning) => $inning->balls->map(fn ($b) => [
            'striker_id' => (int) $b->striker_id,
            'non_striker_id' => (int) $b->non_striker_id,
            'bowler_id' => (int) $b->bowler_id,
            'runs_bat' => (int) $b->runs_bat,
            'extra_type' => $b->extra_type,
            'extra_runs' => (int) $b->extra_runs,
            'wicket_type' => $b->wicket_type,
            'out_player_id' => $b->out_player_id ? (int) $b->out_player_id : null,
            'fielder_id' => $b->fielder_id ? (int) $b->fielder_id : null,
        ])->all())->all();

        return FantasyPointsCalculator::calculateMatch($inningsBalls, $rolesById);
    }

    /** Ranked fantasy leaderboard for a single match. */
    public static function leaderboard(Fixture $fixture, ?Collection $pool = null, ?array $pointsById = null): array
    {
        $pool ??= self::pool($fixture);
        $pointsById ??= self::matchPoints($fixture, $pool);

        $entries = $fixture->fantasyEntries()
            ->with(['players:id', 'captain:id,name', 'viceCaptain:id,name'])
            ->orderBy('created_at')
            ->get();

        $scored = $entries->map(function (FantasyEntry $entry) use ($pointsById) {
            $playerIds = $entry->players->pluck('id')->map(fn ($id) => (int) $id)->all();
            $score = FantasyPointsCalculator::entryScore(
                $pointsById,
                $playerIds,
                (int) $entry->captain_id,
                (int) $entry->vice_captain_id
            );

            return [
                'entry' => $entry,
                'points' => $score,
                'player_ids' => $playerIds,
            ];
        })->sort(function ($a, $b) {
            return $b['points'] <=> $a['points']
                ?: $a['entry']->created_at <=> $b['entry']->created_at;
        })->values();

        $ranked = [];
        $rank = 0;
        $prevPoints = null;
        foreach ($scored as $i => $row) {
            if ($prevPoints === null || $row['points'] < $prevPoints) {
                $rank = $i + 1;
                $prevPoints = $row['points'];
            }
            $row['rank'] = $rank;
            $ranked[] = $row;
        }

        return $ranked;
    }

    /** Tournament-wide Cric11 leaderboard across all matches. */
    public static function overallLeaderboard(): array
    {
        $fixtures = Fixture::whereHas('fantasyEntries')->with('innings.balls')->get();
        if ($fixtures->isEmpty()) {
            return [];
        }

        $totals = [];
        foreach ($fixtures as $fixture) {
            $pool = self::pool($fixture);
            $pointsById = self::matchPoints($fixture, $pool);
            $entries = $fixture->fantasyEntries()->with('players:id')->get();

            // Take a participant's best-scoring team per match for fair overall ranking.
            $bestByMobile = [];
            foreach ($entries as $entry) {
                $score = FantasyPointsCalculator::entryScore(
                    $pointsById,
                    $entry->players->pluck('id')->map(fn ($id) => (int) $id)->all(),
                    (int) $entry->captain_id,
                    (int) $entry->vice_captain_id
                );
                $mobile = $entry->participant_mobile;
                if (! isset($bestByMobile[$mobile]) || $score > $bestByMobile[$mobile]['points']) {
                    $bestByMobile[$mobile] = [
                        'name' => $entry->participant_name,
                        'points' => $score,
                    ];
                }
            }

            foreach ($bestByMobile as $mobile => $best) {
                $totals[$mobile] ??= [
                    'mobile' => $mobile,
                    'name' => $best['name'],
                    'points' => 0.0,
                    'matches' => 0,
                ];
                $totals[$mobile]['name'] = $best['name'];
                $totals[$mobile]['points'] = round($totals[$mobile]['points'] + $best['points'], 1);
                $totals[$mobile]['matches']++;
            }
        }

        usort($totals, fn ($a, $b) => $b['points'] <=> $a['points'] ?: $b['matches'] <=> $a['matches']);

        return array_slice($totals, 0, 20);
    }
}
