<?php

namespace Tests\Feature;

use App\Models\Ball;
use App\Models\FantasyEntry;
use App\Models\Fixture;
use App\Models\Inning;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FantasyTest extends TestCase
{
    use RefreshDatabase;

    private function seedMatch(): array
    {
        $teamA = Team::create(['name' => 'Parnera Lions', 'short_name' => 'PRL', 'purse' => 10000]);
        $teamB = Team::create(['name' => 'School Tigers', 'short_name' => 'SCT', 'purse' => 10000]);

        $roles = ['Wicket Keeper', 'Batsman', 'Batsman', 'Batsman', 'All Rounder', 'All Rounder', 'Bowler', 'Bowler', 'Bowler', 'Wicket Keeper', 'Batsman'];
        $playersA = [];
        $playersB = [];
        for ($i = 0; $i < 11; $i++) {
            $playersA[] = Player::create([
                'name' => "Alpha {$i}", 'mobile' => '98000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'dob' => '2000-01-01', 'village' => 'Parnera', 'role' => $roles[$i],
                'batting_style' => 'Right Hand', 'bowling_style' => 'Right Arm Medium',
                'tshirt_size' => 'M', 'status' => 'approved', 'team_id' => $teamA->id, 'fantasy_credits' => 8.5,
            ]);
            $playersB[] = Player::create([
                'name' => "Beta {$i}", 'mobile' => '99000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'dob' => '2000-01-01', 'village' => 'Parnera', 'role' => $roles[$i],
                'batting_style' => 'Right Hand', 'bowling_style' => 'Right Arm Fast',
                'tshirt_size' => 'L', 'status' => 'approved', 'team_id' => $teamB->id, 'fantasy_credits' => 8.5,
            ]);
        }

        $fixture = Fixture::create([
            'match_no' => 1, 'stage' => 'League', 'team_a_id' => $teamA->id, 'team_b_id' => $teamB->id,
            'starts_at' => now()->addDay(), 'overs' => 8, 'players_per_side' => 11, 'status' => 'upcoming',
        ]);

        return [$fixture, $teamA, $teamB, $playersA, $playersB];
    }

    public function test_cric11_hub_and_team_creation_and_live_scoring(): void
    {
        [$fixture, $teamA, $teamB, $playersA, $playersB] = $this->seedMatch();

        $this->get(route('cric11.index'))->assertOk()->assertSee('Build Your Dream XI');
        $this->get(route('cric11.create', $fixture))->assertOk()->assertSee('Alpha 0');

        // Pick 6 from Team A + 5 from Team B (valid roles: 2 WK, 3 BAT, 2 AR, 4 BOWL; 11 * 8.5 = 93.5 <= 100)
        $picked = [
            $playersA[0]->id, $playersA[1]->id, $playersA[2]->id, $playersA[4]->id, $playersA[6]->id, $playersA[7]->id,
            $playersB[0]->id, $playersB[1]->id, $playersB[4]->id, $playersB[6]->id, $playersB[7]->id,
        ];

        $res = $this->post(route('cric11.store', $fixture), [
            'participant_name' => 'Kamlesh',
            'participant_mobile' => '9876543210',
            'team_name' => 'Kamlesh XI',
            'players' => $picked,
            'captain_id' => $playersA[1]->id,
            'vice_captain_id' => $playersB[6]->id,
        ]);
        $res->assertRedirect(route('cric11.match', $fixture));

        $entry = FantasyEntry::first();
        $this->assertNotNull($entry);
        $this->assertEquals(11, $entry->players()->count());

        // Owner can view upcoming team pitch
        $this->get(route('cric11.entry', $entry))->assertOk()->assertSee('Kamlesh XI');

        // Other browser cannot peek upcoming team
        $this->flushSession();
        $this->get(route('cric11.entry', $entry))->assertForbidden();

        // Match goes live -> teams lock and live balls score points
        $fixture->update(['status' => 'live']);
        $this->get(route('cric11.create', $fixture))->assertForbidden();
        $this->get(route('cric11.entry', $entry))->assertOk();

        $inning = Inning::create([
            'fixture_id' => $fixture->id, 'number' => 1,
            'batting_team_id' => $teamA->id, 'bowling_team_id' => $teamB->id,
        ]);
        Ball::create([
            'inning_id' => $inning->id, 'striker_id' => $playersA[1]->id, 'non_striker_id' => $playersA[0]->id,
            'bowler_id' => $playersB[6]->id, 'runs_bat' => 6, 'extra_type' => null, 'extra_runs' => 0,
        ]);

        // Captain (Alpha 1): 4 play + 6 runs + 2 six bonus = 12 * 2x = 24
        // Alpha 0: 4 play = 4
        // Bowler Beta 6: 4 play = 4 * 1.5x = 6
        // Total = 34
        $this->get(route('cric11.match', $fixture))
            ->assertOk()
            ->assertSee('Kamlesh XI')
            ->assertSee('34');
    }

    public function test_invalid_selections_are_rejected(): void
    {
        [$fixture, $teamA, $teamB, $playersA, $playersB] = $this->seedMatch();

        // >7 players from one team
        $tooManyFromA = array_merge(
            array_map(fn ($p) => $p->id, array_slice($playersA, 0, 8)),
            array_map(fn ($p) => $p->id, array_slice($playersB, 0, 3))
        );
        $this->post(route('cric11.store', $fixture), [
            'participant_name' => 'Test User',
            'participant_mobile' => '9876543211',
            'team_name' => 'Bad XI',
            'players' => $tooManyFromA,
            'captain_id' => $playersA[0]->id,
            'vice_captain_id' => $playersA[1]->id,
        ])->assertSessionHasErrors();

        $this->assertEquals(0, FantasyEntry::count());
    }
}
