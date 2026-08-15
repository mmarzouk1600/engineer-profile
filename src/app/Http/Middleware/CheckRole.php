<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserGroupType; // Assuming you have an Enum for user group types

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated as an admin
        if (auth('admin')->check()) {

            // Check if the user has the required role
            if (!empty(array_intersect(auth('admin')->user()->getRoleNames()->pluck('id')->toArray(), $roles))) {
                return $next($request); // User has the required role, proceed with the request
            }

            // Redirect based on user type and route
            if (auth('admin')->user()->hasRole('student') && (\Route::is('admin.*'))) {
                return redirect('/'); // Redirect to the home for students accessing 'admin' routes
            } else if (!auth('admin')->user()->hasRole('student') && (\Route::is('students.*') || \Route::is('home'))) {
                return redirect(RouteServiceProvider::ADMIN_PREFIX); // Redirect to dashboard for non-students accessing 'students.*' or 'home' routes
            }
        }

        // Redirect to a default route or URL if not authenticated or doesn't have the required role
        return redirect('/login'); // Change '/login' to your desired route
    }
}
