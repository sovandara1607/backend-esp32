<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('device_type'); // e.g. 'esp32', 'esp8266', 'arduino'
            $table->string('device_identifier')->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
