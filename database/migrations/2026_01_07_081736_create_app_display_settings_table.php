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
        Schema::create('app_display_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('font_size', 3, 1)->default(16.0); // Font size slider value
            $table->enum('appearance', ['light', 'dark'])->default('dark'); // Light or Dark mode
            $table->boolean('tajweed_color_guide')->default(false); // Tajweed Color Guide toggle
            $table->string('arabic_script')->nullable()->default('uthmani'); // Arabic script type
            $table->string('typography_background_color', 20)->default('white'); // Background color for typography
            $table->string('translation_by')->nullable(); // Translation options by whom   
            // Advanced Settings
            $table->string('delay_between_verse', 20)->default('none'); // Delay between verse: none, 0.5 sec, 1 sec, 2 sec, 5 sec, 10 sec, 30 sec, ayat_length
            $table->enum('playback_speed', ['slower', 'normal', 'faster'])->default('normal'); // Playback speed
            $table->boolean('stop_after_range')->default(false); // Stop after range toggle
            $table->boolean('stream_without_downloading')->default(false); // Stream without downloading toggle
            $table->boolean('scroll_while_playing')->default(true); // Scroll while playing toggle
            $table->boolean('word_by_word_audio_highlighting')->default(true); // Word by word audio highlighting toggle
            // Audio Settings
            $table->string('qari')->nullable(); // Qari name (e.g., "Abdul Muhsin al qasim")
            $table->string('repeat_verse', 20)->default('none'); // Repeat verse option: none, 1, 2, 3, etc.
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }
     
    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('app_display_settings');
    }
};
