<?php

namespace App\Providers;

use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Accounts\Services\AuditLog;
use App\Domain\Accounts\Services\PermissionCache;
use App\Models\User;
use App\Policies\ClassPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }
        Route::model('schoolClass', SchoolClass::class);
        Gate::policy(SchoolClass::class, ClassPolicy::class);

        RateLimiter::for('login', function (Request $request): array {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(3)->by($request->input('email', 'guest')),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('email-verification', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
        });

        Event::listen(Login::class, function (Login $event): void {
            AuditLog::record($event->user, 'login');
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLog::record($event->user, 'logout');
        });

        Event::listen(Registered::class, function (Registered $event): void {
            AuditLog::record($event->user, 'registered');
        });

        User::updated(function (User $user): void {
            if ($user->wasChanged('role')) {
                PermissionCache::flush($user);
            }
        });
    }
}
