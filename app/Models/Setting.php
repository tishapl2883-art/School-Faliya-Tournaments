<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    /** All settings as ['key' => 'value']. */
    public static function map(): array
    {
        return static::pluck('value', 'key')->all();
    }

    public static function get(string $key, $default = null)
    {
        return static::find($key)?->value ?? $default;
    }
}
