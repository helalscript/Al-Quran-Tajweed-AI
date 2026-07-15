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
        Schema::table('dua_translations', function (Blueprint $table) {
            $table->fullText(
                ['title', 'translation'],
                'dua_translations_fulltext'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dua_translations', function (Blueprint $table) {
            $table->dropFullText('dua_translations_fulltext');
        });
    }
};
