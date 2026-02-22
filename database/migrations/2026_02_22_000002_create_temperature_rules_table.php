<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('temperature_rules', function (Blueprint $table) {
         $table->id();
         $table->foreignId('temperature_profile_id')->constrained('temperature_profiles')->onDelete('cascade');
         $table->decimal('temperature', 5, 1);
         $table->unsignedTinyInteger('fan_speed_percent');
         $table->timestamps();

         $table->index(['temperature_profile_id', 'temperature']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('temperature_rules');
   }
};
