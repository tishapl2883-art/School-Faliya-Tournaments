<?php

namespace App\Services;

/** Pure points-table logic: 2 points for a win, 1 for a tie or no result, then net run rate. */
class PointsTable
{
    /**
     * @param array $teamIds
     * @param array $matches each: team_a, team_b, result_type (win|tie|no_result), winner,
     *                       innings => [[team, runs, balls, all_out, quota_balls], ...]
     */
    public static function build(array $teamIds, array $matches): array
    {
        $rows = [];
        foreach ($teamIds as $id) {
            $rows[$id] = ['team_id' => $id, 'played' => 0, 'won' => 0, 'lost' => 0, 'tied' => 0, 'points' => 0,
                'rf' => 0, 'bf' => 0, 'ra' => 0, 'bb' => 0, 'nrr' => 0.0];
        }

        foreach ($matches as $m) {
            $a = $m['team_a'];
            $b = $m['team_b'];
            if (! isset($rows[$a], $rows[$b])) {
                continue;
            }
            $rows[$a]['played']++;
            $rows[$b]['played']++;

            if ($m['result_type'] === 'win') {
                $loser = $m['winner'] == $a ? $b : $a;
                $rows[$m['winner']]['won']++;
                $rows[$m['winner']]['points'] += 2;
                $rows[$loser]['lost']++;
            } else {
                foreach ([$a, $b] as $id) {
                    $rows[$id]['tied']++;
                    $rows[$id]['points']++;
                }
            }

            if ($m['result_type'] === 'no_result' || count($m['innings']) < 2) {
                continue;
            }
            foreach ($m['innings'] as $i) {
                $batting = $i['team'];
                $bowling = $batting == $a ? $b : $a;
                // A side bowled out counts as having used its full overs.
                $balls = $i['all_out'] ? $i['quota_balls'] : $i['balls'];
                $rows[$batting]['rf'] += $i['runs'];
                $rows[$batting]['bf'] += $balls;
                $rows[$bowling]['ra'] += $i['runs'];
                $rows[$bowling]['bb'] += $balls;
            }
        }

        foreach ($rows as &$r) {
            $for = $r['bf'] ? $r['rf'] * 6 / $r['bf'] : 0;
            $against = $r['bb'] ? $r['ra'] * 6 / $r['bb'] : 0;
            $r['nrr'] = round($for - $against, 3);
        }
        unset($r);

        usort($rows, fn ($x, $y) => [$y['points'], $y['nrr'], $y['won']] <=> [$x['points'], $x['nrr'], $x['won']]);

        return $rows;
    }
}
