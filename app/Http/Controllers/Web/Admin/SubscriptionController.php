<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the subscriptions.
     */
    public function index(Request $request)
    {
        $query = Subscription::with('user');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(15);

        return Inertia::render('admin/subscriptions/index', [
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Display the specified subscription.
     */
    public function show(string $id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);

        return Inertia::render('admin/subscriptions/show', [
            'subscription' => $subscription
        ]);
    }

    /**
     * Remove the specified subscription from storage.
     */
    public function destroy(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription deleted successfully');
    }
}
