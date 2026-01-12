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
        Schema::create('memorization_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('surah_id')->nullable(); // Surah number/ID
            $table->string('surah_name')->nullable(); // Surah name (alternative to surah_id)
            $table->integer('ayah_id')->nullable(); // Single ayah ID (for single ayah memorization)
            $table->string('ayah_text')->nullable(); // Ayah text in surah
            $table->integer('ayah_start')->nullable(); // Starting ayah (for range)
            $table->integer('ayah_end')->nullable(); // Ending ayah (for range)
            $table->text('original_text'); // Original Arabic text from Quran (fetched from surah+ayah)
            $table->text('user_recitation'); // User's recited text (voice converted)
            $table->enum('status', ['in_progress', 'completed', 'paused', 'cancelled'])->default('in_progress');
            $table->decimal('accuracy_score', 5, 2)->nullable(); // AI calculated accuracy (0-100)
            $table->integer('total_mistakes')->default(0);
            $table->text('ai_response')->nullable(); // Full AI analysis response
            $table->datetime('started_at');
            $table->datetime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorization_sessions');
    }
};
