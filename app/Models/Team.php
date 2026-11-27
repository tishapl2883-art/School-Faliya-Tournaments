<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $guarded = [];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function spent(): int
    {
        return (int) $this->players()->sum('sold_price');
    }

    public function remaining(): int
    {
        return $this->purse - $this->spent();
    }
}
