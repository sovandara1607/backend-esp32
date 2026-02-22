<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemperatureRule extends Model
{
    protected $fillable = [
        'temperature_profile_id',
        'temperature',
        'fan_speed_percent',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'fan_speed_percent' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TemperatureProfile::class, 'temperature_profile_id');
    }
}
