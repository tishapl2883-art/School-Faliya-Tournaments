<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->decimal('fantasy_credits', 4, 1)->nullable()->after('sold_price');
        });

        Schema::create('fantasy_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->string('participant_name', 80);
            $table->string('participant_mobile', 15)->index();
            $table->string('team_name', 60);
            $table->foreignId('captain_id')->constrained('players')->restrictOnDelete();
            $table->foreignId('vice_captain_id')->constrained('players')->restrictOnDelete();
            $table->decimal('total_credits', 5, 1);
            $table->string('edit_token', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('fantasy_entry_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unique(['fantasy_entry_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fantasy_entry_players');
        Schema::dropIfExists('fantasy_entries');
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('fantasy_credits');
        });
    }
};
