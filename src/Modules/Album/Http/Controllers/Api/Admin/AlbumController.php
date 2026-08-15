<?php

namespace Modules\Album\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Enums\AlbumStatus;
use Modules\Album\Http\Requests\StoreAlbumRequest;
use Modules\Album\Http\Requests\UpdateAlbumRequest;
use Modules\Album\Services\AlbumService;
use Modules\Album\Transformers\AlbumDetailResource;
use Modules\Album\Transformers\AlbumResource;

/**
 * @OA\Tag(
 *     name="Admin Albums",
 *     description="Admin management of albums (create, edit, publish, delete)"
 * )
 */
class AlbumController extends Controller
{
    public function __construct(private AlbumService $albumService) {}

    /**
     * @OA\Get(
     *     path="/api/admin/albums",
     *     summary="List all albums (draft + published) with search/filter",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"draft","published"})),
     *     @OA\Response(response=200, description="Paginated list of albums"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Album::class);

        return AlbumResource::collection(
            $this->albumService->listAdmin($request->only(['search', 'status']))
        );
    }

    /**
     * @OA\Post(
     *     path="/api/admin/albums",
     *     summary="Create a new album",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","price"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="currency", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft","published"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Album created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreAlbumRequest $request)
    {
        $this->authorize('create', Album::class);

        $album = $this->albumService->create($request->validated(), $request->user()->id);

        return new AlbumDetailResource($album->load(['coverImage', 'images', 'files']));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/albums/{slug}",
     *     summary="Get full album details for editing",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Album details")
     * )
     */
    public function show(Album $album)
    {
        $this->authorize('view', $album);

        $album->load(['coverImage', 'images', 'files'])->loadCount(['images', 'files']);

        return new AlbumDetailResource($album);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/albums/{slug}",
     *     summary="Update an album",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Album updated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $this->authorize('update', $album);

        $album = $this->albumService->update($album, $request->validated());

        return new AlbumDetailResource($album->loadCount(['images', 'files']));
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/albums/{slug}",
     *     summary="Delete (soft-delete) an album",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=204, description="Album deleted")
     * )
     */
    public function destroy(Album $album)
    {
        $this->authorize('delete', $album);

        $this->albumService->delete($album);

        return response()->json(['status' => 'success', 'message' => 'Album deleted.']);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/publish-toggle",
     *     summary="Toggle an album between draft and published",
     *     tags={"Admin Albums"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Status toggled")
     * )
     */
    public function togglePublish(Album $album)
    {
        $this->authorize('update', $album);

        $next = $album->status === AlbumStatus::Published ? AlbumStatus::Draft : AlbumStatus::Published;
        $album = $this->albumService->update($album, ['status' => $next->value]);

        return new AlbumResource($album);
    }
}
