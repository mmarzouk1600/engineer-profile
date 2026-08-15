<?php

namespace Modules\Album\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Services\AlbumService;
use Modules\Album\Transformers\AlbumDetailResource;
use Modules\Album\Transformers\AlbumResource;

/**
 * @OA\Tag(
 *     name="Albums",
 *     description="Public browsing & search of published engineering albums"
 * )
 */
class AlbumController extends Controller
{
    public function __construct(private AlbumService $albumService) {}

    /**
     * @OA\Get(
     *     path="/api/albums",
     *     summary="List published albums (Pinterest-style grid, paginated)",
     *     tags={"Albums"},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string"), description="Search by title/description"),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of published albums")
     * )
     */
    public function index(Request $request)
    {
        $albums = $this->albumService->listPublic($request->only('search'));

        return AlbumResource::collection($albums);
    }

    /**
     * @OA\Get(
     *     path="/api/albums/{slug}",
     *     summary="Get a published album's details (images, files metadata, price)",
     *     tags={"Albums"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Album details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Album $album)
    {
        $user = auth('api')->user();
        $publishedOnly = ! ($user && $user->isAdmin());

        if ($publishedOnly && $album->status->value !== 'published') {
            abort(404);
        }

        $album->load(['coverImage', 'images', 'files'])->loadCount(['images', 'files']);

        return new AlbumDetailResource($album);
    }
}
