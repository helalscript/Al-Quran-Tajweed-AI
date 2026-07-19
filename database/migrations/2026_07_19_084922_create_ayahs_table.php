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
        Schema::create('ayahs', function (Blueprint $table) {
            $table->id('number');
            $table->foreignId('surah_id')->constrained('surahs', 'number')->cascadeOnDelete();
            $table->integer('number_in_surah');
            $table->integer('juz');
            $table->integer('page');
            $table->integer('ruku');
            $table->integer('hizb_quarter');
            $table->boolean('sajda')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayahs');
    }
};
