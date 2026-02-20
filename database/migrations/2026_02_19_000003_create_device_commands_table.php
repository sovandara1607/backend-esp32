<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('device_commands', function (Blueprint $table) {
         $table->id();
         $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
         $table->foreignId('user_id')->constrained()->onDelete('cascade');
         $table->string('command'); // on, off, set_speed, restart, configure
         $table->json('payload')->nullable();
         $table->enum('status', ['pending', 'sent', 'executed', 'failed'])->default('pending');
         $table->text('response')->nullable();
         $table->timestamp('executed_at')->nullable();
         $table->timestamps();

         $table->index(['device_id', 'status']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('device_commands');
   }
};
