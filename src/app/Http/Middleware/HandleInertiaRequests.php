<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Modules\Ticket\Entities\Ticket;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Sets the root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @return string
     */
    public function rootView(Request $request)
    {
        // Path-based check rather than Route::is('admin.*') — deterministic
        // regardless of route-name resolution/caching state.
        return $request->is('dashboard', 'dashboard/*') ? 'app' : 'front';
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ?? null,
                'can' => [],
                'authority' =>    [],
                'team' =>  [],
                'is_delegated' =>true ,
                'has_delegated' => true,
                'roles' =>  [],
                'notifications' => $user?->unreadNotifications,
                'is_local' => \App::environment('local')
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
                'icon' => $request->session()->get('icon')
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'locale' => function () {
                return app()->getLocale();
            },
            'language' => function () {
                $content = ['ratingsQuestions' => Lang::get('ticket::ratingsQuestions')];
                if(!file_exists(base_path('lang/front/' .app()->getLocale(). '.json')))
                    return $content;
                return (json_decode(
                    file_get_contents(
                        base_path('lang/front/' .app()->getLocale(). '.json')
                    ), true
                ) ?? []) + $content;
            },
        ]);
    }
}
