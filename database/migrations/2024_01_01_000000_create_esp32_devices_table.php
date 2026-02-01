<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esp32_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->integer('interval')->default(3600); // Sleep interval in seconds
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // For additional device info
            $table->timestamps();
            
            $table->index('hostname');
            $table->index('last_seen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esp32_devices');
    }
};
