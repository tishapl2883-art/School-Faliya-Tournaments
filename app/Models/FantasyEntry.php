<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A fan's Cric11 fantasy team entry for a single match. */
class FantasyEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_credits' => 'float',
    ];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function captain()
    {
        return $this->belongsTo(Player::class, 'captain_id');
    }

    public function viceCaptain()
    {
        return $this->belongsTo(Player::class, 'vice_captain_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'fantasy_entry_players');
    }
}
