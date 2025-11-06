<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('app_languages')->insert([
            ['name' => 'English', 'code' => 'en', 'flag_icon' => 'uploads/language-flags/en.png', 'is_default' => true],
            ['name' => 'Arabic', 'code' => 'ar', 'flag_icon' => 'uploads/language-flags/ar.png', 'is_default' => false],
            ['name' => 'Bahasa Indonesia', 'code' => 'id', 'flag_icon' => 'uploads/language-flags/id.png', 'is_default' => false],
            ['name' => 'Bahasa Melayu', 'code' => 'ms', 'flag_icon' => 'uploads/language-flags/ms.png', 'is_default' => false],
            ['name' => 'Bangla', 'code' => 'bn', 'flag_icon' => 'uploads/language-flags/bn.png', 'is_default' => false],
            ['name' => 'Francis', 'code' => 'fr', 'flag_icon' => 'uploads/language-flags/fr.png', 'is_default' => false],
            ['name' => 'Urdu', 'code' => 'ur', 'flag_icon' => 'uploads/language-flags/ur.png', 'is_default' => false],
        ]);
    }
}
