<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Check for existing session BEFORE authenticating
        $credentials = $request->only('email', 'password');
        
        // Try to get the user first to check existing sessions
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if ($user) {
            // Check if user already has an active session (single device login)
            $existingSession = UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingSession) {
                // Log out the existing session immediately
                if ($existingSession->session_id) {
                    DB::table('sessions')->where('id', $existingSession->session_id)->delete();
                }

                // Update the existing session record
                $existingSession->update([
                    'status' => 'logged_out',
                    'logout_at' => now(),
                ]);
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        // Update UserSession with the NEW session ID (Login event created it with old ID)
        UserSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest('login_at')
            ->first()
            ?->update(['session_id' => session()->getId()]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
