<?php

namespace Database\Seeders;

use App\Models\DuaDhikir;
use Illuminate\Database\Seeder;

class DuaDhikirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DuaDhikir::factory()->count(25)->create();
    }
}
