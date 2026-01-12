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
        Schema::create('memorization_mistakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memorization_session_id')->constrained()->onDelete('cascade');
            $table->enum('mistake_type', ['missing_word', 'wrong_word', 'extra_word', 'pronunciation'])->default('wrong_word');
            $table->integer('word_position')->nullable(); // Position in original text (word index)
            $table->integer('sentence_position')->nullable(); // Position in sentence (for Flutter to identify)
            $table->string('original_word'); // What should be
            $table->string('user_word')->nullable(); // What user said
            $table->decimal('confidence_score', 5, 2)->nullable(); // AI confidence (0-100)
            $table->text('suggestion')->nullable(); // AI suggestion for correction
            $table->boolean('is_corrected')->default(false); // Whether user corrected this mistake
            $table->datetime('corrected_at')->nullable(); // When user corrected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorization_mistakes');
    }
};
