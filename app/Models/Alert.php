<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
   protected $fillable = [
      'device_id',
      'user_id',
      'alert_type',
      'sensor_type',
      'condition',
      'threshold_value',
      'message',
      'severity',
      'is_read',
      'is_active',
      'triggered_at',
   ];

   protected function casts(): array
   {
      return [
         'threshold_value' => 'decimal:2',
         'is_read' => 'boolean',
         'is_active' => 'boolean',
         'triggered_at' => 'datetime',
      ];
   }

   public function device(): BelongsTo
   {
      return $this->belongsTo(Device::class);
   }

   public function user(): BelongsTo
   {
      return $this->belongsTo(User::class);
   }
}
