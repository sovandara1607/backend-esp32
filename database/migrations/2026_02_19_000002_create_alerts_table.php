<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('alerts', function (Blueprint $table) {
         $table->id();
         $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
         $table->foreignId('user_id')->constrained()->onDelete('cascade');
         $table->string('alert_type'); // threshold, offline, custom
         $table->string('sensor_type')->nullable();
         $table->string('condition')->nullable(); // above, below, equals
         $table->decimal('threshold_value', 10, 2)->nullable();
         $table->string('message');
         $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
         $table->boolean('is_read')->default(false);
         $table->boolean('is_active')->default(true);
         $table->timestamp('triggered_at')->nullable();
         $table->timestamps();

         $table->index(['user_id', 'is_read']);
         $table->index(['device_id', 'alert_type']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('alerts');
   }
};
