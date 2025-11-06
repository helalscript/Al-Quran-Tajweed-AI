<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'title' => 'Prayer Times (Salat Times)',
                'description' => 'Get accurate prayer times based on your location with customizable notification settings for each prayer.',
                // 'image' => 'uploads/features/1.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Quran (with Translation & Tafsir)',
                'description' => 'Access the complete Quran in Arabic with translation in multiple languages and Tafsir for in-depth understanding. Choose from various font styles for better readability.',
                // 'image' => 'uploads/features/2.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Qibla Direction',
                'description' => 'Find the accurate Qibla direction using GPS-based location services, ensuring proper orientation for prayer.',
                // 'image' => 'uploads/features/3.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Tajweed Error Detection and Correction',
                'description' => 'Detect and correct Tajweed errors during Quran recitation to ensure accurate pronunciation of Arabic letters and words.',
                // 'image' => 'uploads/features/4.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'AI-Powered Quran Recitation Feedback',
                'description' => 'Receive real-time AI-powered feedback on your Quran recitation, helping you improve your Tajweed and pronunciation accuracy.',
                // 'image' => 'uploads/features/5.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Quran Text + Audio Recitation',
                'description' => 'Read and listen to the Quran with synchronized audio recitations from multiple Qari (reciters). Supports a wide range of recitation styles and speeds.',
                // 'image' => 'uploads/features/6.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Multiple Quran Fonts',
                'description' => 'Choose from various font styles for reading the Quran text, including Uthmani, Indo-Pak, and Madani script, to suit your reading preferences.',
                // 'image' => 'uploads/features/7.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Multiple Recitation Styles',
                'description' => 'Select from a range of renowned Qaris (reciters), including Al-Shuraym, Al-Minshawi, Al-Afasy, and more, to experience different styles of Quran recitation.',
                // 'image' => 'uploads/features/8.png',
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Ad-Free Experience',
                'description' => 'Enjoy an uninterrupted, ad-free experience with all features of the app. Perfect for users who want to focus on their prayers and Quran recitation without distractions.',
                // 'image' => 'uploads/features/9.png',
                'access_level' => 'premium',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Multiple Language Support',
                'description' => 'Access the app in a variety of languages, including English, Arabic, Urdu, Bengali, Turkish, French, and many more. The app is designed to serve a global Muslim audience.',
                'image' => 'uploads/features/10.png',
                'access_level' => 'unlimited', // You can adjust this to be part of a premium plan if necessary
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ]
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}
