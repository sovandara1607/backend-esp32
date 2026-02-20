<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
   protected $table = 'sensor_data';

   protected $fillable = [
      'device_id',
      'sensor_type',
      'value',
      'unit',
      'recorded_at',
   ];

   protected function casts(): array
   {
      return [
         'value' => 'decimal:2',
         'recorded_at' => 'datetime',
      ];
   }

   public function device(): BelongsTo
   {
      return $this->belongsTo(Device::class);
   }
}
