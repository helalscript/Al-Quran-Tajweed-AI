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
        Schema::create('dua_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dua_dhikir_id')->constrained('dua_dhikirs')->onDelete('cascade');
            $table->string('language_code', 10);
            $table->string('title');
            $table->text('translation')->nullable();
            $table->text('notes')->nullable();
            $table->text('benefits')->nullable();
            $table->text('fawaid')->nullable();
            $table->timestamps();
            
            $table->unique(['dua_dhikir_id', 'language_code']);
            $table->index('language_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dua_translations');
    }
};
