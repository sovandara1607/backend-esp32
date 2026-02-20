<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('sensor_data', function (Blueprint $table) {
         $table->id();
         $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
         $table->string('sensor_type'); // temperature, humidity, light, motion, pressure, gas
         $table->decimal('value', 10, 2);
         $table->string('unit')->nullable(); // °C, %, lux, ppm, hPa
         $table->timestamp('recorded_at')->useCurrent();
         $table->timestamps();

         $table->index(['device_id', 'sensor_type', 'recorded_at']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('sensor_data');
   }
};
