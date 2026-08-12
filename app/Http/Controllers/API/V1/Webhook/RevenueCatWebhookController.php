<?php

namespace App\Http\Controllers\API\V1\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RevenueCatWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from RevenueCat.
     * Note: Make sure to disable CSRF protection for this route if using web.php.
     */
    public function handle(Request $request)
    {
        // For security, you can verify the authorization header here using REVENUECAT_WEBHOOK_SECRET
        $secret = config('revenue-cat.webhook.secret') ?? env('REVENUECAT_WEBHOOK_SECRET');
        $authHeader = $request->header('Authorization');

        if ($secret && $authHeader !== 'Bearer ' . $secret) {
            Log::warning('RevenueCat Webhook: Invalid Authorization Header.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        
        if (!$event) {
            return response()->json(['message' => 'No event payload'], 400);
        }

        $eventType = $event['type'] ?? null;
        $appUserId = $event['app_user_id'] ?? null;
        $productId = $event['product_id'] ?? null;
        $entitlementId = $event['entitlement_ids'][0] ?? null; // usually just one
        
        $expirationAtMs = $event['expiration_at_ms'] ?? null;
        $purchasedAtMs = $event['purchased_at_ms'] ?? null;

        Log::info("RevenueCat Webhook Received: {$eventType} for user {$appUserId}");

        if ($appUserId) {
            $status = 'active'; // Default active

            if (in_array($eventType, ['CANCELLATION', 'EXPIRATION', 'BILLING_ISSUE'])) {
                $status = 'canceled'; // Or expired
            }

            // Find subscription by rc_original_app_user_id
            $subscription = Subscription::where('rc_original_app_user_id', $appUserId)->first();

            if ($subscription) {
                $subscription->status = $status;
                if ($productId) $subscription->product_id = $productId;
                if ($entitlementId) $subscription->entitlement_id = $entitlementId;
                
                if ($expirationAtMs) {
                    $subscription->expires_at = Carbon::createFromTimestampMs($expirationAtMs);
                }
                if ($purchasedAtMs) {
                    $subscription->purchased_at = Carbon::createFromTimestampMs($purchasedAtMs);
                }

                $subscription->save();
            }
        }

        return response()->json(['message' => 'Webhook handled']);
    }
}
