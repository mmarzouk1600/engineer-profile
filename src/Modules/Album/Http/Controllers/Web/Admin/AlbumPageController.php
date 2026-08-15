<?php

namespace Modules\Album\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Album\Entities\Album;

class AlbumPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Albums/Index');
    }

    public function create(): Response
    {
        return Inertia::render('Albums/Edit', [
            'albumSlug' => null,
        ]);
    }

    public function edit(Album $album): Response
    {
        return Inertia::render('Albums/Edit', [
            'albumSlug' => $album->slug,
        ]);
    }

    public function purchases(): Response
    {
        return Inertia::render('Purchases/Index');
    }
}
