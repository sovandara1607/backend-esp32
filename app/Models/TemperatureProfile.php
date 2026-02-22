<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemperatureProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TemperatureRule::class);
    }

    /**
     * Get rules sorted by temperature ascending.
     */
    public function sortedRules()
    {
        return $this->rules()->orderBy('temperature', 'asc')->get();
    }

    /**
     * Given a current temperature, return the fan speed percent.
     * Uses step-function: highest rule where temp >= rule.temperature.
     * Returns -1 if below all rules (fan off).
     */
    public function evaluateSpeed(float $currentTemp): int
    {
        $matchedSpeed = -1;

        foreach ($this->sortedRules() as $rule) {
            if ($currentTemp >= $rule->temperature) {
                $matchedSpeed = $rule->fan_speed_percent;
            } else {
                break;
            }
        }

        return $matchedSpeed;
    }
}
