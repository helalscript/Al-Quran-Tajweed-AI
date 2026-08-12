<?php

namespace App\Http\Controllers\API\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Sync user subscription from RevenueCat after a purchase.
     */
    public function syncSubscription(Request $request)
    {
        $request->validate([
            'rc_original_app_user_id' => 'required|string',
            'product_id' => 'required|string',
            'entitlement_id' => 'required|string',
            'status' => 'required|string', // active, expired, canceled, etc.
            'expires_at' => 'nullable|date',
            'purchased_at' => 'nullable|date',
        ]);

        $user = auth()->user();

        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id, 'rc_original_app_user_id' => $request->rc_original_app_user_id],
            [
                'product_id' => $request->product_id,
                'entitlement_id' => $request->entitlement_id,
                'status' => $request->status,
                'expires_at' => $request->expires_at,
                'purchased_at' => $request->purchased_at,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription synced successfully.',
            'data' => $subscription
        ]);
    }

    /**
     * Check active subscription status.
     */
    public function checkStatus(Request $request)
    {
        $user = auth()->user();

        // Check if there is an active subscription
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'has_active_subscription' => $activeSubscription ? true : false,
            'data' => $activeSubscription
        ]);
    }
}
