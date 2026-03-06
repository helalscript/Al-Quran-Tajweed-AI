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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('paypal_client_id')->nullable()->after('footer_logo');
            $table->string('paypal_client_secret')->nullable()->after('paypal_client_id');
            $table->string('stripe_public_key')->nullable()->after('paypal_client_secret');
            $table->string('stripe_secret_key')->nullable()->after('stripe_public_key');

            $table->string('ai_api_key')->nullable()->after('stripe_secret_key');

            $table->string('smtp_host')->nullable()->after('ai_api_key');
            $table->unsignedInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->string('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption')->nullable()->after('smtp_password');
            $table->string('smtp_from_address')->nullable()->after('smtp_encryption');
            $table->string('smtp_from_name')->nullable()->after('smtp_from_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_client_id',
                'paypal_client_secret',
                'stripe_public_key',
                'stripe_secret_key',
                'ai_api_key',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'smtp_from_address',
                'smtp_from_name',
            ]);
        });
    }
};
