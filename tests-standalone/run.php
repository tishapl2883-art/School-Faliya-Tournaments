<?php
// Tests the pure scoring, points and auction logic with plain PHP: php tests-standalone/run.php

use App\Services\AuctionRules;
use App\Services\FantasyPointsCalculator as FP;
use App\Services\FantasyRules;
use App\Services\PointsTable;
use App\Services\ScoreCalculator as S;

require __DIR__.'/../app/Services/ScoreCalculator.php';
require __DIR__.'/../app/Services/PointsTable.php';
require __DIR__.'/../app/Services/AuctionRules.php';
require __DIR__.'/../app/Services/FantasyRules.php';
require __DIR__.'/../app/Services/FantasyPointsCalculator.php';

$pass = $fail = 0;
function is($expected, $actual, string $what): void
{
    global $pass, $fail;
    if ($expected === $actual) { $pass++; return; }
    $fail++;
    echo "FAIL {$what}: expected ".var_export($expected, true).', got '.var_export($actual, true)."\n";
}
function ball(int $s, int $n, int $runs = 0, ?string $extra = null, ?string $wkt = null, ?int $out = null, int $bowler = 20): array
{
    return S::split($extra, $runs) + ['striker_id' => $s, 'non_striker_id' => $n, 'bowler_id' => $bowler,
        'extra_type' => $extra, 'wicket_type' => $wkt, 'out_player_id' => $out, 'fielder_id' => null];
}

$r = S::calculate([]);
is(0, $r['runs'], 'empty runs'); is('0.0', $r['overs'], 'empty overs');

$r = S::calculate([ball(1, 2, 1)]);
is(2, $r['striker_id'], 'single swaps strike');

$r = S::calculate([ball(1, 2, 0, 'wide')]);
is(1, $r['runs'], 'wide 1 run'); is(0, $r['legal_balls'], 'wide not legal'); is(0, $r['batters'][1]['balls'], 'wide not faced'); is(1, $r['striker_id'], 'plain wide keeps strike');
$r = S::calculate([ball(1, 2, 1, 'wide')]);
is(2, $r['runs'], 'wide + 1'); is(2, $r['striker_id'], 'wide + 1 swaps'); is(2, $r['bowlers'][20]['runs'], 'wides charged');

$r = S::calculate([ball(1, 2, 4, 'noball')]);
is(5, $r['runs'], 'noball four'); is(4, $r['batters'][1]['runs'], 'bat runs'); is(1, $r['batters'][1]['balls'], 'noball faced'); is(true, $r['free_hit'], 'free hit');
$r = S::calculate([ball(1, 2, 0, 'noball'), ball(1, 2, 0, 'wide')]);
is(true, $r['free_hit'], 'free hit stays on wide');
$r = S::calculate([ball(1, 2, 0, 'noball'), ball(1, 2, 0)]);
is(false, $r['free_hit'], 'free hit used');

$r = S::calculate([ball(1, 2, 2, 'legbye'), ball(1, 2, 1, 'bye')]);
is(3, $r['runs'], 'byes total'); is(0, $r['bowlers'][20]['runs'], 'byes not charged'); is(2, $r['striker_id'], 'odd bye swaps');

$maiden = array_fill(0, 6, ball(1, 2, 0));
$r = S::calculate($maiden);
is('1.0', $r['overs'], 'one over'); is(1, $r['bowlers'][20]['maidens'], 'maiden'); is(2, $r['striker_id'], 'end of over swap');
is(null, $r['bowler_id'], 'new bowler needed'); is(20, $r['previous_over_bowler_id'], 'previous bowler');
$r = S::calculate(array_merge($maiden, [ball(2, 1, 1, null, null, null, 21)]));
is(['1'], $r['timeline'], 'timeline resets'); is('1.1', $r['overs'], '1.1');

$r = S::calculate([ball(1, 2, 0, null, 'caught', 1)]);
is(1, $r['wickets'], 'wicket'); is(null, $r['striker_id'], 'new batter on strike'); is(2, $r['non_striker_id'], 'partner stays'); is(1, $r['bowlers'][20]['wickets'], 'bowler credit');
$r = S::calculate([ball(1, 2, 1, null, 'run_out', 2)]);
is(0, $r['bowlers'][20]['wickets'], 'no credit run out'); is(null, $r['striker_id'], 'run out slot after crossing'); is(1, $r['non_striker_id'], 'survivor');
$r = S::calculate(array_merge(array_fill(0, 5, ball(1, 2, 0)), [ball(1, 2, 0, null, 'bowled', 1)]));
is(2, $r['striker_id'], 'wicket last ball: partner takes strike'); is(null, $r['non_striker_id'], 'new batter non striker');

is(null, S::validateDelivery(null, 4, null, false), 'normal ok');
is(true, S::validateDelivery(null, 0, 'bowled', true) !== null, 'no bowled on free hit');
is(true, S::validateDelivery('noball', 0, 'caught', false) !== null, 'no caught on noball');
is(null, S::validateDelivery('wide', 0, 'stumped', false), 'stumped off wide');
is(true, S::validateDelivery(null, 2, 'caught', false) !== null, 'no runs when caught');
is(true, S::validateDelivery('bye', 0, null, false) !== null, 'bye needs runs');

$t = PointsTable::build([1, 2, 3], [[
    'team_a' => 1, 'team_b' => 2, 'result_type' => 'win', 'winner' => 1,
    'innings' => [
        ['team' => 1, 'runs' => 96, 'balls' => 48, 'all_out' => false, 'quota_balls' => 48],
        ['team' => 2, 'runs' => 64, 'balls' => 30, 'all_out' => true, 'quota_balls' => 48],
    ],
], ['team_a' => 2, 'team_b' => 3, 'result_type' => 'no_result', 'winner' => null, 'innings' => []]]);
is(1, $t[0]['team_id'], 'leader'); is(2, $t[0]['points'], 'win pts'); is(4.0, $t[0]['nrr'], 'nrr all out uses quota');
$by = array_column($t, null, 'team_id');
is(1, $by[2]['points'], 'no result point'); is(-4.0, $by[2]['nrr'], 'negative nrr');

$cfg = ['base_price' => 100, 'bid_increment' => 100, 'min_squad' => 11, 'max_squad' => 15];
is(9000, AuctionRules::maxBid(10000, 0, 11, 100), 'max bid keeps reserve');
is(null, AuctionRules::check(100, null, null, 1, 10000, 0, $cfg), 'opening bid');
is(true, AuctionRules::check(150, 100, 2, 1, 10000, 0, $cfg) !== null, 'below increment');
is(true, AuctionRules::check(300, 100, 1, 1, 10000, 0, $cfg) !== null, 'already leading');
is(true, AuctionRules::check(9100, 100, 2, 1, 10000, 0, $cfg) !== null, 'over max');
is(true, AuctionRules::check(200, 100, 2, 1, 10000, 15, $cfg) !== null, 'squad full');

// Cric11 Fantasy Rules & Points tests
$pool = [];
$roles = ['WK', 'BAT', 'BAT', 'BAT', 'AR', 'AR', 'BOWL', 'BOWL', 'BOWL', 'WK', 'BAT'];
for ($i = 1; $i <= 22; $i++) {
    $pool[] = [
        'id' => $i,
        'team_id' => $i <= 11 ? 1 : 2,
        'role_code' => $roles[($i - 1) % 11],
        'credits' => 8.5,
    ];
}
$validTeam = [1, 2, 3, 4, 5, 7, 12, 13, 16, 18, 19]; // 6 from Team 1, 5 from Team 2, all roles covered
is(null, FantasyRules::validateSelection($pool, $validTeam, 1, 2), 'valid 11-player fantasy team');
is(true, FantasyRules::validateSelection($pool, range(1, 10), 1, 2) !== null, 'must pick 11 players');
is(true, FantasyRules::validateSelection($pool, range(1, 11), 1, 1) !== null, 'captain != vice captain');
$overTeam = [1, 2, 3, 4, 5, 6, 7, 8, 12, 13, 14];
is(true, FantasyRules::validateSelection($pool, $overTeam, 1, 2) !== null, 'max 7 per team');
$expensivePool = array_map(fn ($p) => ['credits' => 10.0] + $p, $pool);
is(true, FantasyRules::validateSelection($expensivePool, range(1, 11), 1, 2) !== null, 'credits <= 100');

// Fantasy points calculation: 30 runs off 12 balls (3 fours, 2 sixes) + bowled wicket + maiden + catch
$fBalls = array_merge(
    array_fill(0, 6, ball(1, 2, 0, null, null, null, 20)), // 1 maiden over by bowler 20
    [
        ball(1, 2, 4, null, null, null, 21),
        ball(1, 2, 4, null, null, null, 21),
        ball(1, 2, 4, null, null, null, 21),
        ball(1, 2, 6, null, null, null, 21),
        ball(1, 2, 6, null, null, null, 21),
        ball(1, 2, 6, null, null, null, 21), // 30 runs in 6 balls + 6 maidens = 12 balls total for batter 1 (SR=250)
    ],
    [
        ['striker_id' => 2, 'non_striker_id' => 1, 'bowler_id' => 20, 'runs_bat' => 0, 'extra_type' => null, 'extra_runs' => 0, 'wicket_type' => 'caught', 'out_player_id' => 2, 'fielder_id' => 22],
        ['striker_id' => 3, 'non_striker_id' => 1, 'bowler_id' => 20, 'runs_bat' => 0, 'extra_type' => null, 'extra_runs' => 0, 'wicket_type' => 'bowled', 'out_player_id' => 3, 'fielder_id' => null],
    ]
);
$pts = FP::calculateMatch([$fBalls], [1 => 'Batsman', 2 => 'Batsman', 3 => 'Batsman', 20 => 'Bowler', 21 => 'Bowler', 22 => 'Wicket Keeper']);
// Batter 1: 4(play) + 30(runs) + 3(fours) + 4(2x2 sixes -> wait 3 sixes = 6!) + 4(30-run bonus) + 6(SR>=170) = 53
is(53, $pts[1]['total'], 'batter 30-run milestone + boundaries + SR bonus');
// Batter 2: 4(play) - 2(duck) = 2
is(2, $pts[2]['total'], 'duck penalty for batter');
// Bowler 20: 4(play) + 50(2 wkts) + 8(1 bowled) + 12(1 maiden) = 74 (bowled 8 balls < 12 so no econ)
is(74, $pts[20]['total'], 'bowler 2 wkts + bowled bonus + maiden');
// Fielder 22: 4(play) + 8(catch) = 12
is(12, $pts[22]['total'], 'fielder catch points');
is(239.5, FP::entryScore($pts, [1, 20, 22], 20, 1), 'C 2x and VC 1.5x multiplier');

echo "{$pass} passed, {$fail} failed\n";
exit($fail ? 1 : 0);
