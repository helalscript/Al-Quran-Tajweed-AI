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
        Schema::create('dua_dhikrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('title');
            $table->text('arabic');
            $table->text('latin');
            $table->text('translation');
            $table->text('notes')->nullable();
            $table->text('benefits')->nullable(); // Some files use 'benefits', some use 'fawaid'
            $table->text('fawaid')->nullable();
            $table->string('source')->nullable();
            $table->string('language_code', 10)->default('en'); // en, id
            $table->string('audio_url')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category_id', 'status']);
            $table->index('language_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dua_dhikrs');
    }
};
