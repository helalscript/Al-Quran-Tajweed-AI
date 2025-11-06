<?php

namespace Database\Seeders;

use App\Models\StepperPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StepperPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stepperPages = [
            [
                'title' => 'Never Miss a Prayer Again',
                'description' => 'Accurate prayer times based on your location, with reminders and countdowns. Stay connected to your daily salah, wherever you are. ',
                'image' => 'uploads/stepper/1.png',
                'order_no' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Read, Listen, and Perfect Your Recitation',
                'description' => 'Read the Qur’an in your preferred script, listen to beautiful recitations, and get AI-powered feedback to improve your tajweed.',
                'image' => 'uploads/stepper/2.png',
                'order_no' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Authentic Duas at Your Fingertips',
                'description' => 'Access a collection of authentic duas with translations, transliterations, and audio — for every moment of your life.',
                'image' => 'uploads/stepper/3.png',
                'order_no' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($stepperPages as $page) {
            StepperPage::create($page);
        }
    }
}
