<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prayer_time_notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('fajr')->default(true); // Notification preference for Fajr
            $table->boolean('dhuhr')->default(true); // Notification preference for Dhuhr
            $table->boolean('asr')->default(true); // Notification preference for Asr
            $table->boolean('maghrib')->default(true); // Notification preference for Maghrib
            $table->boolean('isha')->default(true); // Notification preference for Isha
            $table->boolean('sunrise')->default(true); // Notification preference for Sunrise
            $table->boolean('sunset')->default(true); // Notification preference for Sunset
            $table->enum('status', ['active', 'inactive'])->default('active'); // this for other notification in app
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_time_notifications');
    }
};
