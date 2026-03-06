<?php

use App\Models\SystemSetting;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

it('shows the system settings page', function () {
    $user = User::factory()->create();
    SystemSetting::factory()->create();

    actingAs($user);

    $response = get('/settings/system');

    $response->assertSuccessful();
});

it('updates system settings', function () {
    $user = User::factory()->create();
    $setting = SystemSetting::factory()->create();

    actingAs($user);

    $response = put('/settings/system', [
        'paypal_client_id' => 'test-paypal-id',
        'stripe_public_key' => 'pk_test_123',
        'ai_api_key' => 'test-ai-key',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'user@example.com',
        'smtp_password' => 'secret',
        'smtp_encryption' => 'tls',
        'smtp_from_address' => 'noreply@example.com',
        'smtp_from_name' => 'Example App',
    ]);

    $response->assertRedirect();

    $setting->refresh();

    expect($setting->paypal_client_id)->toBe('test-paypal-id')
        ->and($setting->stripe_public_key)->toBe('pk_test_123')
        ->and($setting->ai_api_key)->toBe('test-ai-key')
        ->and($setting->smtp_host)->toBe('smtp.example.com');
});
