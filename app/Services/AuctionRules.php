<?php

namespace App\Services;

/** Pure auction rules. */
class AuctionRules
{
    /** Highest bid a team may place while keeping enough points to fill its minimum squad. */
    public static function maxBid(int $remaining, int $squad, int $minSquad, int $basePrice): int
    {
        $stillNeeded = max(0, $minSquad - $squad - 1);

        return max(0, $remaining - $stillNeeded * $basePrice);
    }

    public static function nextBid(?int $current, int $basePrice, int $increment): int
    {
        return $current === null ? $basePrice : $current + $increment;
    }

    /** Returns an error message, or null when the bid is allowed. */
    public static function check(int $amount, ?int $current, ?int $leaderId, int $teamId, int $remaining, int $squad, array $cfg): ?string
    {
        if ($leaderId === $teamId) {
            return 'This team already holds the highest bid.';
        }
        if ($squad >= $cfg['max_squad']) {
            return 'This team squad is full.';
        }
        $minimum = self::nextBid($current, $cfg['base_price'], $cfg['bid_increment']);
        if ($amount < $minimum) {
            return "The bid must be at least {$minimum}.";
        }
        $max = self::maxBid($remaining, $squad, $cfg['min_squad'], $cfg['base_price']);
        if ($amount > $max) {
            return "This team can bid at most {$max}.";
        }

        return null;
    }
}
