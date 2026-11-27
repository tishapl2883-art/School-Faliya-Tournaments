<?php

namespace App\Services;

/**
 * Pure Dream11 / Cric11 fantasy points engine (no database, no framework).
 * Derives every player's batting, bowling, fielding, and bonus points directly from ball-by-ball data.
 */
class FantasyPointsCalculator
{
    public const RULES = [
        'Playing in Match' => '+4',
        'Run Scored' => '+1 per run',
        'Boundary (4) Bonus' => '+1',
        'Six (6) Bonus' => '+2',
        '30 / 50 / 100 Run Bonus' => '+4 / +8 / +16',
        'Duck (Batter/WK/AR)' => '-2',
        'Wicket (excl. Run Out)' => '+25',
        'Bowled / LBW Bonus' => '+8',
        '3 / 4 / 5 Wicket Haul' => '+4 / +8 / +16',
        'Maiden Over' => '+12',
        'Catch' => '+8 (+4 for 3+ catches)',
        'Stumping / Run Out' => '+12',
        'Strike Rate (min 10 balls, non-Bowler)' => '+6 / +4 / +2 or -2 / -4 / -6',
        'Economy Rate (min 2 overs)' => '+6 / +4 / +2 or -2 / -4 / -6',
        'Captain / Vice-Captain' => '2x / 1.5x',
    ];

    /**
     * Calculate per-player points for a match.
     *
     * @param  array<int, array<int, array>>  $inningsBalls  List of innings, each a list of ball arrays
     * @param  array<int, string>  $rolesById  Map of player_id => role name (e.g. 'Bowler')
     * @return array<int, array>
     */
    public static function calculateMatch(array $inningsBalls, array $rolesById = []): array
    {
        $stats = [];
        $ensure = function (int $id) use (&$stats): void {
            $stats[$id] ??= [
                'played' => false,
                'runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'dismissed' => false,
                'bowl_balls' => 0, 'bowl_runs' => 0, 'wickets' => 0, 'bowled_lbw' => 0, 'maidens' => 0,
                'catches' => 0, 'stumpings' => 0, 'run_outs' => 0,
            ];
        };

        foreach ($inningsBalls as $balls) {
            if (! $balls) {
                continue;
            }
            $calc = ScoreCalculator::calculate($balls);

            foreach ($calc['batters'] as $id => $b) {
                $id = (int) $id;
                $ensure($id);
                $stats[$id]['played'] = true;
                $stats[$id]['runs'] += $b['runs'];
                $stats[$id]['balls'] += $b['balls'];
                $stats[$id]['fours'] += $b['fours'];
                $stats[$id]['sixes'] += $b['sixes'];
                if ($b['out'] !== null) {
                    $stats[$id]['dismissed'] = true;
                }
            }

            foreach ($calc['bowlers'] as $id => $b) {
                $id = (int) $id;
                $ensure($id);
                $stats[$id]['played'] = true;
                $stats[$id]['bowl_balls'] += $b['balls'];
                $stats[$id]['bowl_runs'] += $b['runs'];
                $stats[$id]['wickets'] += $b['wickets'];
                $stats[$id]['maidens'] += $b['maidens'];
            }

            foreach ($balls as $ball) {
                if (empty($ball['wicket_type'])) {
                    continue;
                }
                if (in_array($ball['wicket_type'], ['bowled', 'lbw'], true)) {
                    $bid = (int) $ball['bowler_id'];
                    $ensure($bid);
                    $stats[$bid]['bowled_lbw']++;
                }
                if (! empty($ball['fielder_id'])) {
                    $fid = (int) $ball['fielder_id'];
                    $ensure($fid);
                    $stats[$fid]['played'] = true;
                    match ($ball['wicket_type']) {
                        'caught' => $stats[$fid]['catches']++,
                        'stumped' => $stats[$fid]['stumpings']++,
                        'run_out' => $stats[$fid]['run_outs']++,
                        default => null,
                    };
                }
            }
        }

        $results = [];
        foreach ($stats as $id => $st) {
            $role = $rolesById[$id] ?? 'Batsman';
            $playing = $st['played'] ? 4 : 0;

            // Batting points
            $bat = $st['runs'] + $st['fours'] + ($st['sixes'] * 2);
            if ($st['runs'] >= 100) {
                $bat += 16;
            } elseif ($st['runs'] >= 50) {
                $bat += 8;
            } elseif ($st['runs'] >= 30) {
                $bat += 4;
            }
            if ($st['dismissed'] && $st['runs'] === 0 && $role !== 'Bowler') {
                $bat -= 2;
            }
            if ($st['balls'] >= 10 && $role !== 'Bowler') {
                $sr = ($st['runs'] * 100) / $st['balls'];
                $bat += match (true) {
                    $sr >= 170 => 6,
                    $sr >= 150 => 4,
                    $sr >= 130 => 2,
                    $sr < 50 => -6,
                    $sr < 60 => -4,
                    $sr <= 70 => -2,
                    default => 0,
                };
            }

            // Bowling points
            $bowl = ($st['wickets'] * 25) + ($st['bowled_lbw'] * 8) + ($st['maidens'] * 12);
            if ($st['wickets'] >= 5) {
                $bowl += 16;
            } elseif ($st['wickets'] >= 4) {
                $bowl += 8;
            } elseif ($st['wickets'] >= 3) {
                $bowl += 4;
            }
            if ($st['bowl_balls'] >= 12) {
                $econ = ($st['bowl_runs'] * 6) / $st['bowl_balls'];
                $bowl += match (true) {
                    $econ < 5 => 6,
                    $econ < 6 => 4,
                    $econ <= 7 => 2,
                    $econ > 12 => -6,
                    $econ > 11 => -4,
                    $econ >= 10 => -2,
                    default => 0,
                };
            }

            // Fielding points
            $field = ($st['catches'] * 8) + ($st['catches'] >= 3 ? 4 : 0)
                + ($st['stumpings'] * 12) + ($st['run_outs'] * 12);

            $results[$id] = [
                'playing' => $playing,
                'batting' => $bat,
                'bowling' => $bowl,
                'fielding' => $field,
                'total' => $playing + $bat + $bowl + $field,
                'stats' => $st,
            ];
        }

        return $results;
    }

    /** Total points for a fantasy entry including C (2x) and VC (1.5x). */
    public static function entryScore(array $pointsById, array $playerIds, int $captainId, int $viceCaptainId): float
    {
        $sum = 0.0;
        foreach ($playerIds as $pid) {
            $base = (float) ($pointsById[$pid]['total'] ?? 0);
            $mult = $pid === $captainId ? 2.0 : ($pid === $viceCaptainId ? 1.5 : 1.0);
            $sum += $base * $mult;
        }

        return round($sum, 1);
    }

    /** Format points neatly without trailing .0 (e.g. 142, 87.5). */
    public static function format(float|int $points): string
    {
        $rounded = round((float) $points, 1);

        return floor($rounded) == $rounded ? (string) (int) $rounded : number_format($rounded, 1);
    }

    /** Compute the highest-scoring valid Dream Team XI from a pool. */
    public static function dreamTeam(array $pool, array $pointsById): ?array
    {
        $limits = FantasyRules::limits($pool);
        $size = $limits['team_size'];
        if ($size === 0) {
            return null;
        }

        $ranked = $pool;
        usort($ranked, function ($a, $b) use ($pointsById) {
            $pa = $pointsById[$a['id']]['total'] ?? 0;
            $pb = $pointsById[$b['id']]['total'] ?? 0;

            return $pb <=> $pa ?: $a['credits'] <=> $b['credits'];
        });

        $picked = [];
        $teamCounts = [];
        $roleCounts = ['WK' => 0, 'BAT' => 0, 'AR' => 0, 'BOWL' => 0];
        $creditsLeft = $limits['max_credits'];

        $canPick = function (array $p, int $slotsLeftAfterThis) use (&$picked, &$teamCounts, &$roleCounts, &$creditsLeft, $limits): bool {
            if (isset($picked[$p['id']])) {
                return false;
            }
            if (round($creditsLeft - $p['credits'], 1) < 0) {
                return false;
            }
            if (($teamCounts[$p['team_id']] ?? 0) >= $limits['max_per_team']) {
                return false;
            }
            $rc = $p['role_code'];
            if (($roleCounts[$rc] ?? 0) >= $limits['roles'][$rc]['max']) {
                return false;
            }
            // Reserve slots for unmet role minimums.
            $unmet = 0;
            foreach ($limits['roles'] as $code => $rule) {
                $have = $roleCounts[$code] + ($code === $rc ? 1 : 0);
                if ($have < $rule['min']) {
                    $unmet += ($rule['min'] - $have);
                }
            }

            return $unmet <= $slotsLeftAfterThis;
        };

        for ($slot = 0; $slot < $size; $slot++) {
            $slotsAfter = $size - $slot - 1;
            foreach ($ranked as $p) {
                if ($canPick($p, $slotsAfter)) {
                    $picked[$p['id']] = $p;
                    $teamCounts[$p['team_id']] = ($teamCounts[$p['team_id']] ?? 0) + 1;
                    $roleCounts[$p['role_code']]++;
                    $creditsLeft = round($creditsLeft - $p['credits'], 1);
                    break;
                }
            }
        }

        if (count($picked) !== $size || $size < 2) {
            return null;
        }

        $ids = array_keys($picked);
        $captainId = $ids[0];
        $viceCaptainId = $ids[1];

        return [
            'player_ids' => $ids,
            'captain_id' => $captainId,
            'vice_captain_id' => $viceCaptainId,
            'total_points' => self::entryScore($pointsById, $ids, $captainId, $viceCaptainId),
            'total_credits' => round($limits['max_credits'] - $creditsLeft, 1),
        ];
    }
}
