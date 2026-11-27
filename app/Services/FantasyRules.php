<?php

namespace App\Services;

/**
 * Pure Dream11 / Cric11 team-building validation (no database, no framework).
 * Each pool player: ['id' => int, 'team_id' => int, 'role_code' => string, 'credits' => float].
 */
class FantasyRules
{
    public const MAX_CREDITS = 100.0;
    public const STANDARD_SIZE = 11;
    public const MAX_PER_TEAM = 7;
    public const MAX_ENTRIES_PER_USER = 3;

    public const ROLE_BOUNDS = [
        'WK' => ['label' => 'Wicket Keeper', 'min' => 1, 'max' => 4],
        'BAT' => ['label' => 'Batsman', 'min' => 1, 'max' => 6],
        'AR' => ['label' => 'All Rounder', 'min' => 1, 'max' => 6],
        'BOWL' => ['label' => 'Bowler', 'min' => 1, 'max' => 6],
    ];

    /** Compute allowed squad limits for a match's available player pool. */
    public static function limits(array $pool): array
    {
        $total = count($pool);
        $teamSize = $total >= self::STANDARD_SIZE ? self::STANDARD_SIZE : $total;

        $teamCounts = [];
        $roleCounts = ['WK' => 0, 'BAT' => 0, 'AR' => 0, 'BOWL' => 0];
        foreach ($pool as $p) {
            $tid = (int) $p['team_id'];
            $teamCounts[$tid] = ($teamCounts[$tid] ?? 0) + 1;
            $code = $p['role_code'];
            if (isset($roleCounts[$code])) {
                $roleCounts[$code]++;
            }
        }

        $minTeamPool = $teamCounts ? min($teamCounts) : 0;
        $maxPerTeam = $teamSize === self::STANDARD_SIZE
            ? max(self::MAX_PER_TEAM, $teamSize - $minTeamPool)
            : $teamSize;

        $roles = [];
        $remainingMinBudget = $teamSize;
        foreach (self::ROLE_BOUNDS as $code => $cfg) {
            $avail = $roleCounts[$code];
            $min = $avail > 0 && $remainingMinBudget > 0 ? min($cfg['min'], $avail, $remainingMinBudget) : 0;
            $remainingMinBudget -= $min;
            $max = $teamSize === self::STANDARD_SIZE
                ? min($avail, max($cfg['max'], $teamSize - ($total - $avail)))
                : $avail;
            $roles[$code] = [
                'label' => $cfg['label'],
                'available' => $avail,
                'min' => $min,
                'max' => max($min, $max),
            ];
        }

        return [
            'team_size' => $teamSize,
            'max_credits' => self::MAX_CREDITS,
            'max_per_team' => $maxPerTeam,
            'roles' => $roles,
        ];
    }

    /** Returns an error message, or null when the fantasy XI + C/VC selection is valid. */
    public static function validateSelection(array $pool, array $selectedIds, ?int $captainId, ?int $viceCaptainId): ?string
    {
        $limits = self::limits($pool);
        if ($limits['team_size'] === 0) {
            return 'No approved squad players are available for this match yet.';
        }

        $byId = [];
        foreach ($pool as $p) {
            $byId[(int) $p['id']] = $p;
        }

        $uniqueIds = array_values(array_unique(array_map('intval', $selectedIds)));
        if (count($uniqueIds) !== $limits['team_size']) {
            return "Select exactly {$limits['team_size']} players for your Cric11 team.";
        }

        $credits = 0.0;
        $teamCounts = [];
        $roleCounts = ['WK' => 0, 'BAT' => 0, 'AR' => 0, 'BOWL' => 0];
        foreach ($uniqueIds as $id) {
            if (! isset($byId[$id])) {
                return 'One or more selected players do not belong to this match.';
            }
            $p = $byId[$id];
            $credits += (float) $p['credits'];
            $tid = (int) $p['team_id'];
            $teamCounts[$tid] = ($teamCounts[$tid] ?? 0) + 1;
            $roleCounts[$p['role_code']] = ($roleCounts[$p['role_code']] ?? 0) + 1;
        }

        if (round($credits, 1) > $limits['max_credits']) {
            return 'Total credits cannot exceed '.number_format($limits['max_credits'], 1).'.';
        }

        foreach ($teamCounts as $count) {
            if ($count > $limits['max_per_team']) {
                return "You can pick at most {$limits['max_per_team']} players from one team.";
            }
        }

        foreach ($limits['roles'] as $code => $rule) {
            $count = $roleCounts[$code] ?? 0;
            if ($count < $rule['min'] || $count > $rule['max']) {
                return "{$rule['label']} ({$code}) count must be between {$rule['min']} and {$rule['max']}.";
            }
        }

        if (! $captainId || ! in_array($captainId, $uniqueIds, true)) {
            return 'Select a Captain (2x) from your 11 players.';
        }
        if (! $viceCaptainId || ! in_array($viceCaptainId, $uniqueIds, true)) {
            return 'Select a Vice-Captain (1.5x) from your 11 players.';
        }
        if ($captainId === $viceCaptainId) {
            return 'Captain and Vice-Captain must be different players.';
        }

        return null;
    }
}
