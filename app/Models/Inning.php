<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inning extends Model
{
    protected $table = 'innings';
    protected $guarded = [];
    protected $casts = ['completed' => 'boolean'];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function battingTeam()
    {
        return $this->belongsTo(Team::class, 'batting_team_id');
    }

    public function bowlingTeam()
    {
        return $this->belongsTo(Team::class, 'bowling_team_id');
    }

    public function balls()
    {
        return $this->hasMany(Ball::class)->orderBy('id');
    }
}
