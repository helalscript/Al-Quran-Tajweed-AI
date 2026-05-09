<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mail;
use URL;

class PlayStoreRelatedController extends Controller
{
    public function showLoginForm()
    {
        return view('playstore.app_user_login');
    }

    /**
     * login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt to log in the user
        if (Auth::attempt($credentials)) {
            $user = Auth::user(); // Get the authenticated user

            // Check if the user's role is admin
            if ($user->role === 'admin') {
                Auth::logout(); // Log out the user if they are not an admin
                return back()->withErrors(['email' => 'You do not have admin privileges.']);
            }

            return redirect()->route('app.user.dashboard');
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function dashboard()
    {
        //check user is auth 
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('app.user.login');
        }
        return view('playstore.app_user_dashboard', ['user' => Auth::user()]);
    }

    // This method handles sending the deletion link via email
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if (empty($user)) {
            return redirect()->route('app.user.login');
        }
        // Generate a unique token for account deletion
        $token = Str::random(60);

        // Store the token in the user's record
        $user->delete_token = $token;
        $user->save();

        // Generate a signed URL for account deletion
        $deleteUrl = URL::temporarySignedRoute(
            'account.delete.confirmation',
            now()->addMinutes(30),
            ['token' => $token]
        );
        Log::info('deleteUrl: '.$deleteUrl);
        // Send the deletion confirmation email with the link
        Mail::to($user->email)->send(new AccountDeletionMail($deleteUrl));

        // Redirect with a success message
        return redirect()->route('app.user.dashboard')->with('message', 'An account deletion link has been sent to your email.');
    }
    public function confirmAccountDeletion($token)
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect()->route('app.user.login');
        }
        // Find the user based on the token
        $user = User::where('delete_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        // Perform the account deletion
        $user->forceDelete();
        return redirect()->route('confirm.delete.account')->with('message', 'Account deleted successfully.');
    }

    public function ConfirmDeleteAccount()
    {
        return view('playstore.confirm_delete_account');
    }


    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->flash('success', 'Logout Successfully');

        return redirect()->route('app.user.login');
    }
}
