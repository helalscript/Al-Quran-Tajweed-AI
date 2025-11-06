<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create packages
        $freePackage = Package::create([
            'title' => 'Free Plan',
            'price_weekly' => null,
            'price_monthly' => 0,
            'price_yearly' => null,
            'price_offer' => null,
            'free_trail_day' => 0,
            'description' => 'Get the full Al-Quran experience with essential features.',
            // 'image' => 'uploads/packages/pro-package.png',
            'status' => 'active',
        ]);

        $basicPackage = Package::create([
            'title' => 'Basic Plan',
            'price_weekly' => null,
            'price_monthly' => 16.99,
            'price_yearly' => 16.99 * 12 * 0.9,
            'price_offer' => null,
            'free_trail_day' => 7,
            'description' => 'Get the full Al-Quran experience with essential features, including AI-powered verse suggestions, but with some limitations.',
            // 'image' => 'uploads/packages/basic-package.png',
            'status' => 'active',
        ]);

        $premiumPackage = Package::create([
            'title' => 'Premium Plan',
            'price_weekly' => null,
            'price_monthly' => 29.99,
            'price_yearly' => 29.99 * 12 * 0.8,
            'price_offer' => null,
            'free_trail_day' => 7,
            'description' => 'Get the full Al-Quran experience with essential features, including AI-powered verse suggestions, but without some limitations.',
            // 'image' => 'uploads/packages/premium-package.png',
            'status' => 'active',
        ]);

        // Define the feature IDs for each plan
        $freeFeatures = [1, 2, 3]; // Feature IDs for Free Plan
        $basicFeatures = [1, 2, 3, 4, 5, 6, 7, 8, 10]; // Feature IDs for Basic Plan
        $premiumFeatures = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Feature IDs for Premium Plan

        // Attach features to the corresponding packages
        $this->attachFeaturesToPackage($freePackage, $freeFeatures);
        $this->attachFeaturesToPackage($basicPackage, $basicFeatures);
        $this->attachFeaturesToPackage($premiumPackage, $premiumFeatures);
    }

    /**
     * Attach features to a package.
     *
     * @param Package $package
     * @param array $featureIds
     */
    protected function attachFeaturesToPackage(Package $package, array $featureIds)
    {
        foreach ($featureIds as $featureId) {
            $feature = Feature::find($featureId);
            if ($feature) {
                $package->features()->attach($feature->id, [
                    'description' => $feature->description,
                    'image' => $feature->image??null,  // Assign the actual image if needed
                    'status' => 'active',
                ]);
            }
        }
    }
}
