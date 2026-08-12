<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    /**
     * Show the system settings page.
     */
    public function edit(Request $request): Response
    {
        $setting = [
            'paypal_client_id' => config('services.paypal.client_id'),
            'paypal_client_secret' => config('services.paypal.client_secret'),
            'paypal_mode' => config('services.paypal.mode'),
            'paypal_webhook_id' => config('services.paypal.webhook_id'),
            'stripe_public_key' => config('services.stripe.key'),
            'stripe_secret_key' => config('services.stripe.secret'),
            'stripe_mode' => config('services.stripe.mode'),
            'stripe_webhook_secret' => config('services.stripe.webhook_secret'),
            'ai_api_key' => config('services.ai.api_key'),
            'revenuecat_api_key' => env('REVENUECAT_API_KEY'),
            'revenuecat_project_id' => env('REVENUECAT_PROJECT_ID'),
            'revenuecat_webhook_secret' => env('REVENUECAT_WEBHOOK_SECRET'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'smtp_password' => config('mail.mailers.smtp.password'),
            'smtp_encryption' => config('mail.mailers.smtp.scheme'),
            'smtp_from_address' => config('mail.from.address'),
            'smtp_from_name' => config('mail.from.name'),
        ];

        return Inertia::render('admin/system-settings', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update the system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_client_secret' => ['nullable', 'string', 'max:255'],
            'paypal_mode' => ['nullable', 'string', 'in:sandbox,live'],
            'paypal_webhook_id' => ['nullable', 'string', 'max:255'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'stripe_mode' => ['nullable', 'string', 'in:test,live'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'ai_api_key' => ['nullable', 'string', 'max:255'],
            'revenuecat_api_key' => ['nullable', 'string', 'max:255'],
            'revenuecat_project_id' => ['nullable', 'string', 'max:255'],
            'revenuecat_webhook_secret' => ['nullable', 'string', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['nullable', 'string', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->updateEnvironmentValues([
            'PAYPAL_CLIENT_ID' => $validated['paypal_client_id'] ?? null,
            'PAYPAL_CLIENT_SECRET' => $validated['paypal_client_secret'] ?? null,
            'PAYPAL_MODE' => $validated['paypal_mode'] ?? null,
            'PAYPAL_WEBHOOK_ID' => $validated['paypal_webhook_id'] ?? null,
            'STRIPE_KEY' => $validated['stripe_public_key'] ?? null,
            'STRIPE_SECRET' => $validated['stripe_secret_key'] ?? null,
            'STRIPE_MODE' => $validated['stripe_mode'] ?? null,
            'STRIPE_WEBHOOK_SECRET' => $validated['stripe_webhook_secret'] ?? null,
            'AI_API_KEY' => $validated['ai_api_key'] ?? null,
            'REVENUECAT_API_KEY' => $validated['revenuecat_api_key'] ?? null,
            'REVENUECAT_PROJECT_ID' => $validated['revenuecat_project_id'] ?? null,
            'REVENUECAT_WEBHOOK_SECRET' => $validated['revenuecat_webhook_secret'] ?? null,
            'MAIL_HOST' => $validated['smtp_host'] ?? null,
            'MAIL_PORT' => $validated['smtp_port'] ?? null,
            'MAIL_USERNAME' => $validated['smtp_username'] ?? null,
            'MAIL_PASSWORD' => $validated['smtp_password'] ?? null,
            'MAIL_SCHEME' => $validated['smtp_encryption'] ?? null,
            'MAIL_FROM_ADDRESS' => $validated['smtp_from_address'] ?? null,
            'MAIL_FROM_NAME' => $validated['smtp_from_name'] ?? null,
        ]);

        config([
            'services.paypal.client_id' => $validated['paypal_client_id'] ?? null,
            'services.paypal.client_secret' => $validated['paypal_client_secret'] ?? null,
            'services.paypal.mode' => $validated['paypal_mode'] ?? null,
            'services.paypal.webhook_id' => $validated['paypal_webhook_id'] ?? null,
            'services.stripe.key' => $validated['stripe_public_key'] ?? null,
            'services.stripe.secret' => $validated['stripe_secret_key'] ?? null,
            'services.stripe.mode' => $validated['stripe_mode'] ?? null,
            'services.stripe.webhook_secret' => $validated['stripe_webhook_secret'] ?? null,
            'services.ai.api_key' => $validated['ai_api_key'] ?? null,
            'mail.mailers.smtp.host' => $validated['smtp_host'] ?? null,
            'mail.mailers.smtp.port' => $validated['smtp_port'] ?? null,
            'mail.mailers.smtp.username' => $validated['smtp_username'] ?? null,
            'mail.mailers.smtp.password' => $validated['smtp_password'] ?? null,
            'mail.mailers.smtp.scheme' => $validated['smtp_encryption'] ?? null,
            'mail.from.address' => $validated['smtp_from_address'] ?? null,
            'mail.from.name' => $validated['smtp_from_name'] ?? null,
        ]);

        return back();
    }

    /**
     * Update the .env file with the given key / value pairs.
     */
    private function updateEnvironmentValues(array $values): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $envPath = base_path('.env');

        if (! file_exists($envPath) || ! is_writable($envPath)) {
            return;
        }

        $env = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            $escaped = $this->escapeEnvValue((string) $value);
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                $env = (string) preg_replace($pattern, "{$key}={$escaped}", $env);
            } else {
                $env .= PHP_EOL."{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $env);
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (strpbrk($value, " \t#\"'") !== false) {
            $value = str_replace('"', '\"', $value);

            return "\"{$value}\"";
        }

        return $value;
    }
}
