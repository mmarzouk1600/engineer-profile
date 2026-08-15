<?php

namespace App\Http\Middleware;

use App\Enums\UserGroupType;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {

            if ($guard == "admin" && Auth::guard($guard)->check()){// && auth('admin')->user()->type != UserGroupType::student ) {
                return redirect(RouteServiceProvider::ADMIN_PREFIX);
            }

            if (Auth::guard($guard)->check()) {
                // return redirect(RouteServiceProvider::HOME);
                return redirect(RouteServiceProvider::ADMIN_PREFIX);
            }
        }

        return $next($request);
    }
}
