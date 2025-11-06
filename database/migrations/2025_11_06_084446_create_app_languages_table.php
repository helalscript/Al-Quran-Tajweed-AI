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
        Schema::create('app_languages', function (Blueprint $table) {
            $table->id(); // Primary key ID
            $table->string('name'); // The name of the language (e.g., English, Arabic)
            $table->string('code', 10)->unique(); // A unique code for each language (e.g., 'en', 'ar')
            $table->string('flag_icon')->nullable(); // Flag icon (optional)
            $table->boolean('is_default')->default(false); // Mark a default language
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status of the language
            $table->timestamps(); // Created at & Updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_languages');
    }
};
