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
        Schema::create('ayah_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayah_id')->constrained('ayahs', 'number')->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained('editions')->cascadeOnDelete();
            $table->text('text')->nullable();
            $table->string('audio_url')->nullable();
            $table->timestamps();

            // Indexes for fast full-text searching
            $table->index('ayah_id');
            $table->index('edition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayah_editions');
    }
};
