<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Route::is('admin.*')) return $next($request);
        if ($this->checkRoles()) return $next($request);
        Auth::guard('admin')->logout();
        return to_route('admin.login')->with(['message' => 'عذرا ليس لديك أي صلاحيات للوصول إلى النظام', 'icon' => 'error']);
    }

    private function checkRoles()
    {
        if(!Auth::guard('admin')->check()) return true;
        $admin = Auth::guard('admin')->user();

        return true;
    }
}