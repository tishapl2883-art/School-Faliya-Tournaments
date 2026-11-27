<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionState extends Model
{
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
