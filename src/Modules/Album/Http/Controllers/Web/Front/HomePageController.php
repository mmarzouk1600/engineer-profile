<?php

namespace Modules\Album\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomePageController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'meta' => [
                'title' => config('app.name') . ' — Engineering Drawings & Project Albums',
                'description' => 'Browse civil engineering project albums, preview drawings, and purchase downloadable files.',
            ],
        ]);
    }
}
