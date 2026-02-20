<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
   protected $fillable = [
      'device_id',
      'user_id',
      'command',
      'payload',
      'status',
      'response',
      'executed_at',
   ];

   protected function casts(): array
   {
      return [
         'payload' => 'array',
         'executed_at' => 'datetime',
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