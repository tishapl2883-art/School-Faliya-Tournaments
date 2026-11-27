<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });

        Schema::create('teams', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('short_name', 6);
            $t->string('owner_name')->nullable();
            $t->string('captain_name')->nullable();
            $t->string('logo')->nullable();
            $t->unsignedInteger('purse');
            $t->timestamps();
        });

        Schema::create('players', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('mobile', 15)->unique();
            $t->date('dob');
            $t->string('village');
            $t->string('role');
            $t->string('batting_style');
            $t->string('bowling_style');
            $t->string('tshirt_size', 5);
            $t->string('jersey_name', 20)->nullable();
            $t->unsignedSmallInteger('jersey_number')->nullable();
            $t->string('photo')->nullable();
            $t->string('payment_ref')->nullable();
            $t->string('payment_proof')->nullable();
            $t->string('status')->default('pending')->index();          // pending | approved | rejected
            $t->string('auction_status')->default('pending')->index();  // pending | live | sold | unsold
            $t->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedInteger('sold_price')->nullable();
            $t->timestamps();
        });

        // One row only: the player currently under the hammer.
        Schema::create('auction_states', function (Blueprint $t) {
            $t->id();
            $t->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('bids', function (Blueprint $t) {
            $t->id();
            $t->foreignId('player_id')->constrained()->cascadeOnDelete();
            $t->foreignId('team_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('amount');
            $t->timestamps();
        });

        Schema::create('fixtures', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('match_no');
            $t->string('stage')->default('League');
            $t->foreignId('team_a_id')->constrained('teams')->restrictOnDelete();
            $t->foreignId('team_b_id')->constrained('teams')->restrictOnDelete();
            $t->dateTime('starts_at');
            $t->unsignedTinyInteger('overs')->default(8);
            $t->unsignedTinyInteger('players_per_side')->default(11);
            $t->string('status')->default('upcoming')->index(); // upcoming | live | completed
            $t->foreignId('toss_winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $t->string('toss_decision')->nullable();            // bat | bowl
            $t->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $t->string('result_type')->nullable();              // win | tie | no_result
            $t->string('result_text')->nullable();
            $t->timestamps();
        });

        Schema::create('innings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('number');
            $t->foreignId('batting_team_id')->constrained('teams')->restrictOnDelete();
            $t->foreignId('bowling_team_id')->constrained('teams')->restrictOnDelete();
            $t->boolean('completed')->default(false);
            $t->timestamps();
            $t->unique(['fixture_id', 'number']);
        });

        Schema::create('balls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inning_id')->constrained('innings')->cascadeOnDelete();
            $t->foreignId('striker_id')->constrained('players')->restrictOnDelete();
            $t->foreignId('non_striker_id')->constrained('players')->restrictOnDelete();
            $t->foreignId('bowler_id')->constrained('players')->restrictOnDelete();
            $t->unsignedTinyInteger('runs_bat')->default(0);
            $t->string('extra_type')->nullable();               // wide | noball | bye | legbye
            $t->unsignedTinyInteger('extra_runs')->default(0);
            $t->string('wicket_type')->nullable();
            $t->foreignId('out_player_id')->nullable()->constrained('players')->restrictOnDelete();
            $t->foreignId('fielder_id')->nullable()->constrained('players')->restrictOnDelete();
            $t->timestamps();
        });

        // Starting event details. All can be changed in Admin > Settings.
        $defaults = [
            'title' => 'Parnera Champions Trophy',
            'brand' => 'Bunty Patel',
            'tagline' => 'School Faliya Boys',
            'organizer' => 'Parnera School Faliya',
            'venue' => 'Chocolate Factory Ground',
            'start_date' => '2026-12-11',
            'end_date' => '2026-12-13',
            'total_teams' => '8',
            'registration_open' => '1',
            'registration_fee' => '0',
            'upi_id' => '',
            'contact_phone' => '',
            'about' => 'Parnera Champions Trophy is a cricket tournament organised by Parnera School Faliya.',
            'team_purse' => '10000',
            'base_price' => '100',
            'bid_increment' => '100',
            'min_squad' => '11',
            'max_squad' => '15',
            'logo' => '',
        ];
        foreach ($defaults as $key => $value) {
            DB::table('settings')->insert(compact('key', 'value'));
        }
        DB::table('auction_states')->insert(['id' => 1]);
    }

    public function down(): void
    {
        foreach (['balls', 'innings', 'fixtures', 'bids', 'auction_states', 'players', 'teams', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
