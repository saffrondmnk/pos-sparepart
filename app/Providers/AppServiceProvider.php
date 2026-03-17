<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserSession;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/CurrencyHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(User::class, UserPolicy::class);

        // Share settings with all views
        View::composer('*', function ($view) {
            $view->with('settings', Setting::getSettings());
        });

        // Track user login
        Event::listen(Login::class, function (Login $event) {
            UserSession::create([
                'user_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => 'active',
            ]);
        });

        // Track user logout
        Event::listen(Logout::class, function (Logout $event) {
            // Find the most recent active session for this user
            $session = UserSession::where('user_id', $event->user->id)
                ->where('status', 'active')
                ->latest('login_at')
                ->first();
            
            if ($session) {
                $session->update([
                    'logout_at' => now(),
                    'status' => 'logged_out',
                ]);
            }
        });
    }
}
