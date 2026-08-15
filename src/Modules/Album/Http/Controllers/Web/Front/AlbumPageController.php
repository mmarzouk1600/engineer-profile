<?php

namespace Modules\Album\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Album\Entities\Album;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlbumPageController extends Controller
{
    public function __invoke(Album $album): Response
    {
        $user = auth('web')->user() ?? auth('api')->user();

        if ($album->status->value !== 'published' && ! ($user && $user->isAdmin())) {
            throw new NotFoundHttpException();
        }

        return Inertia::render('Albums/Show', [
            'slug' => $album->slug,
            'meta' => [
                'title' => $album->title,
                'description' => str($album->description)->limit(160)->value(),
            ],
        ]);
    }
}
