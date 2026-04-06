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
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 100)->unique();
            $table->string('language', 10)->nullable();
            $table->string('name')->nullable();
            $table->string('english_name')->nullable();
            $table->enum('format', ['text', 'audio'])->nullable();
            $table->string('type', 50)->nullable();
            $table->enum('direction', ['ltr', 'rtl'])->nullable()->default('ltr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
