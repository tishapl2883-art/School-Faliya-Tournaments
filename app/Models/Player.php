<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public const ROLES = ['Batsman', 'Bowler', 'All Rounder', 'Wicket Keeper'];
    public const ROLE_CODES = [
        'Wicket Keeper' => 'WK',
        'Batsman' => 'BAT',
        'All Rounder' => 'AR',
        'Bowler' => 'BOWL',
    ];
    public const BATTING = ['Right Hand', 'Left Hand'];
    public const BOWLING = ['Right Arm Fast', 'Right Arm Medium', 'Right Arm Spin', 'Left Arm Fast', 'Left Arm Medium', 'Left Arm Spin', 'Does Not Bowl'];
    public const SIZES = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

    protected $guarded = [];
    protected $casts = ['dob' => 'date'];
    protected $hidden = ['mobile', 'payment_ref', 'payment_proof'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function photoUrl(): string
    {
        return $this->photo ? asset('storage/'.$this->photo) : asset('images/player.svg');
    }

    public function roleCode(): string
    {
        return self::ROLE_CODES[$this->role] ?? 'BAT';
    }

    /** Fantasy credit value (5.0 - 15.0). Uses admin-set value or derives one from role & auction price. */
    public function fantasyCredits(): float
    {
        if ($this->fantasy_credits !== null) {
            return round((float) $this->fantasy_credits, 1);
        }

        $base = $this->role === 'All Rounder' ? 9.0 : 8.5;
        $price = (int) ($this->sold_price ?? 0);
        if ($price >= 2000) {
            $base += 1.0;
        } elseif ($price >= 1000) {
            $base += 0.5;
        } elseif ($price > 0 && $price <= 200) {
            $base -= 0.5;
        }

        return round(min(10.5, max(7.5, $base)), 1);
    }
}
