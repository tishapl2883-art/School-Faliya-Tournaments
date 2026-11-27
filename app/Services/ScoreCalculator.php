<?php

namespace App\Services;

/**
 * Pure cricket scoring logic (no database, no framework).
 * The ball-by-ball list is the only source of truth, so "undo" is just deleting the last ball.
 *
 * Each ball: striker_id, non_striker_id, bowler_id, runs_bat, extra_type, extra_runs,
 *            wicket_type, out_player_id, fielder_id
 */
class ScoreCalculator
{
    public const EXTRAS = ['wide', 'noball', 'bye', 'legbye'];

    public const WICKETS = [
        'bowled' => 'Bowled',
        'caught' => 'Caught',
        'lbw' => 'LBW',
        'run_out' => 'Run Out',
        'stumped' => 'Stumped',
        'hit_wicket' => 'Hit Wicket',
    ];

    public static function isLegal(array $b): bool
    {
        return ! in_array($b['extra_type'], ['wide', 'noball'], true);
    }

    /** Returns an error message, or null when the delivery is allowed. */
    public static function validateDelivery(?string $extra, int $runs, ?string $wicket, bool $freeHit): ?string
    {
        if ($wicket === null) {
            return ($extra === 'bye' || $extra === 'legbye') && $runs < 1 ? 'Byes need at least 1 run.' : null;
        }
        if ($freeHit && $wicket !== 'run_out') {
            return 'Only a run out is possible on a free hit.';
        }
        if ($extra === 'noball' && $wicket !== 'run_out') {
            return 'Only a run out is possible on a no ball.';
        }
        if ($extra === 'wide' && ! in_array($wicket, ['run_out', 'stumped', 'hit_wicket'], true)) {
            return 'On a wide only run out, stumped or hit wicket is possible.';
        }
        if (in_array($extra, ['bye', 'legbye'], true) && $wicket !== 'run_out') {
            return 'Only a run out is possible with byes.';
        }
        if ($wicket !== 'run_out' && $runs > 0) {
            return 'Runs can only be scored on a wicket ball if it is a run out.';
        }

        return null;
    }

    /** Split the entered runs into bat runs and extra runs. */
    public static function split(?string $extra, int $runs): array
    {
        return match ($extra) {
            'wide' => ['runs_bat' => 0, 'extra_runs' => $runs + 1],
            'noball' => ['runs_bat' => $runs, 'extra_runs' => 1],
            'bye', 'legbye' => ['runs_bat' => 0, 'extra_runs' => $runs],
            default => ['runs_bat' => $runs, 'extra_runs' => 0],
        };
    }

    public static function oversText(int $legalBalls): string
    {
        return intdiv($legalBalls, 6).'.'.($legalBalls % 6);
    }

    public static function calculate(array $balls): array
    {
        $s = [
            'runs' => 0, 'wickets' => 0, 'legal_balls' => 0,
            'extras' => ['wide' => 0, 'noball' => 0, 'bye' => 0, 'legbye' => 0],
            'batters' => [], 'bowlers' => [], 'out' => [], 'timeline' => [], 'fall' => [],
            'striker_id' => null, 'non_striker_id' => null, 'bowler_id' => null,
            'previous_over_bowler_id' => null, 'free_hit' => false,
        ];
        $over = []; // current over, for maiden detection and the "this over" strip

        foreach ($balls as $b) {
            $legal = self::isLegal($b);
            $extra = $b['extra_type'];
            $total = $b['runs_bat'] + $b['extra_runs'];
            $striker = $b['striker_id'];
            $bowler = $b['bowler_id'];

            foreach ([$striker, $b['non_striker_id']] as $id) {
                $s['batters'][$id] ??= ['runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'out' => null];
            }
            $s['bowlers'][$bowler] ??= ['balls' => 0, 'runs' => 0, 'wickets' => 0, 'maidens' => 0, 'wides' => 0, 'noballs' => 0];

            if (count($over) && self::legalCount($over) === 6) {
                $over = [];
            }

            $s['runs'] += $total;
            if ($extra) {
                $s['extras'][$extra] += $b['extra_runs'];
            }

            $s['batters'][$striker]['runs'] += $b['runs_bat'];
            if ($extra !== 'wide') {
                $s['batters'][$striker]['balls']++;
            }
            if ($b['runs_bat'] === 4) {
                $s['batters'][$striker]['fours']++;
            }
            if ($b['runs_bat'] === 6) {
                $s['batters'][$striker]['sixes']++;
            }

            $conceded = $b['runs_bat'] + (in_array($extra, ['wide', 'noball'], true) ? $b['extra_runs'] : 0);
            $s['bowlers'][$bowler]['runs'] += $conceded;
            if ($extra === 'wide') {
                $s['bowlers'][$bowler]['wides'] += $b['extra_runs'];
            }
            if ($extra === 'noball') {
                $s['bowlers'][$bowler]['noballs']++;
            }
            if ($legal) {
                $s['legal_balls']++;
                $s['bowlers'][$bowler]['balls']++;
            }

            if ($b['wicket_type']) {
                $outId = $b['out_player_id'];
                $s['wickets']++;
                $s['out'][] = $outId;
                $s['batters'][$outId]['out'] = [
                    'type' => $b['wicket_type'], 'bowler_id' => $bowler, 'fielder_id' => $b['fielder_id'],
                ];
                if ($b['wicket_type'] !== 'run_out') {
                    $s['bowlers'][$bowler]['wickets']++;
                }
                $s['fall'][] = ['player_id' => $outId, 'runs' => $s['runs'], 'wicket' => $s['wickets'], 'overs' => self::oversText($s['legal_balls'])];
            }

            $label = self::label($b);
            $over[] = ['legal' => $legal, 'bowler' => $bowler, 'conceded' => $conceded, 'label' => $label];
            $s['timeline'] = array_column($over, 'label');

            // Strike rotation: odd runs actually run, then end of over.
            $ran = match ($extra) {
                'wide' => $b['extra_runs'] - 1,
                'noball' => $b['runs_bat'],
                'bye', 'legbye' => $b['extra_runs'],
                default => $b['runs_bat'],
            };
            $on = $striker;
            $off = $b['non_striker_id'];
            if ($ran % 2 === 1) {
                [$on, $off] = [$off, $on];
            }

            $s['bowler_id'] = $bowler;
            if ($legal && self::legalCount($over) === 6) {
                [$on, $off] = [$off, $on];
                $same = count(array_unique(array_column($over, 'bowler'))) === 1;
                if ($same && array_sum(array_column($over, 'conceded')) === 0) {
                    $s['bowlers'][$bowler]['maidens']++;
                }
                $s['previous_over_bowler_id'] = $bowler;
                $s['bowler_id'] = null;
            }

            $s['striker_id'] = in_array($on, $s['out']) ? null : $on;
            $s['non_striker_id'] = in_array($off, $s['out']) ? null : $off;

            // A free hit follows a no ball and stays while the next delivery is not legal.
            if ($extra === 'noball') {
                $s['free_hit'] = true;
            } elseif ($legal) {
                $s['free_hit'] = false;
            }
        }

        $s['overs'] = self::oversText($s['legal_balls']);
        $s['run_rate'] = $s['legal_balls'] ? round($s['runs'] * 6 / $s['legal_balls'], 2) : 0;
        $s['extras_total'] = array_sum($s['extras']);

        return $s;
    }

    private static function legalCount(array $over): int
    {
        return count(array_filter($over, fn ($o) => $o['legal']));
    }

    private static function label(array $b): string
    {
        $text = match ($b['extra_type']) {
            'wide' => $b['extra_runs'].'Wd',
            'noball' => ($b['runs_bat'] + 1).'Nb',
            'bye' => $b['extra_runs'].'B',
            'legbye' => $b['extra_runs'].'Lb',
            default => (string) $b['runs_bat'],
        };
        if ($b['wicket_type']) {
            $text = $text === '0' ? 'W' : $text.'+W';
        }

        return $text;
    }
}
