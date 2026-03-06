<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'system_name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_number' => $this->faker->phoneNumber(),
            'company_open_hour' => '09:00 - 17:00',
            'copyright_text' => '© '.now()->year.' '.$this->faker->company(),
            'logo' => null,
            'favicon' => null,
            'address' => $this->faker->address(),
            'description' => $this->faker->sentence(),
            'footer_logo' => null,
            'paypal_client_id' => null,
            'paypal_client_secret' => null,
            'stripe_public_key' => null,
            'stripe_secret_key' => null,
            'ai_api_key' => null,
            'smtp_host' => null,
            'smtp_port' => null,
            'smtp_username' => null,
            'smtp_password' => null,
            'smtp_encryption' => null,
            'smtp_from_address' => null,
            'smtp_from_name' => null,
        ];
    }
}
