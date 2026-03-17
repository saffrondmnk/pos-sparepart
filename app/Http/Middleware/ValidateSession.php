<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ValidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = session()->getId();
            
            // Check if this user has been explicitly force-logged out
            $forceLoggedOutSession = UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('status', 'logged_out')
                ->first();
            
            // Only logout if explicitly marked as logged_out (force logout)
            if ($forceLoggedOutSession) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your session has been terminated by an administrator.');
            }
            
            // Update the session_id if we find an active session without session_id
            // or with a different session_id (session regeneration after login)
            UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($query) use ($sessionId) {
                    $query->whereNull('session_id')
                          ->orWhere('session_id', '!=', $sessionId);
                })
                ->latest('login_at')
                ->first()
                ?->update(['session_id' => $sessionId]);
        }
        
        return $next($request);
    }
}
