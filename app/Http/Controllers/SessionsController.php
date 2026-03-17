<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SessionsController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        
        // Build query based on user role
        $query = UserSession::with('user')->latest('login_at');
        
        if ($currentUser->isSuperAdmin()) {
            // Super admin can see all sessions
            // No additional filter needed
        } elseif ($currentUser->isAdmin()) {
            // Admin can only see cashier sessions
            $query->whereHas('user', function ($q) {
                $q->where('role', 'cashier');
            });
        } else {
            // Cashiers shouldn't access this page at all
            abort(403, 'Unauthorized access');
        }
        
        // Filter by user if specified
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by status if specified
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }
        
        $sessions = $query->paginate(20);
        
        // Get users list for filter dropdown based on role
        if ($currentUser->isSuperAdmin()) {
            $users = User::all();
        } else {
            $users = User::where('role', 'cashier')->get();
        }
        
        // Get summary statistics
        $stats = [
            'total_sessions' => UserSession::count(),
            'active_sessions' => UserSession::where('status', 'active')->count(),
            'today_logins' => UserSession::whereDate('login_at', today())->count(),
        ];
        
        // If admin, recalculate stats for cashiers only
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $stats = [
                'total_sessions' => UserSession::whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
                'active_sessions' => UserSession::where('status', 'active')->whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
                'today_logins' => UserSession::whereDate('login_at', today())->whereHas('user', function ($q) {
                    $q->where('role', 'cashier');
                })->count(),
            ];
        }
        
        return view('sessions.index', compact('sessions', 'users', 'stats'));
    }
    
    public function forceLogout(UserSession $session): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Check permissions
        if ($currentUser->isSuperAdmin()) {
            // Super admin can force logout anyone
        } elseif ($currentUser->isAdmin()) {
            // Admin can only force logout cashiers
            if (!$session->user->isCashier()) {
                abort(403, 'You can only manage cashier sessions');
            }
        } else {
            abort(403, 'Unauthorized access');
        }
        
        // Cannot logout yourself
        if ($session->user_id === $currentUser->id) {
            return redirect()->route('sessions.index')
                ->with('error', 'You cannot force logout your own session');
        }
        
        // Update session status
        $session->update([
            'status' => 'logged_out',
            'logout_at' => now(),
        ]);
        
        return redirect()->route('sessions.index')
            ->with('success', 'User has been force logged out successfully');
    }
}