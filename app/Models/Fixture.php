<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A match. Named Fixture because "match" is a reserved word in PHP. */
class Fixture extends Model
{
    public const STAGES = ['League', 'Quarter Final', 'Semi Final', 'Final'];

    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime'];

    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function tossWinner()
    {
        return $this->belongsTo(Team::class, 'toss_winner_id');
    }

    public function innings()
    {
        return $this->hasMany(Inning::class)->orderBy('number');
    }

    public function fantasyEntries()
    {
        return $this->hasMany(FantasyEntry::class);
    }
}
