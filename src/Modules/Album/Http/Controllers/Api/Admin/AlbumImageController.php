<?php

namespace Modules\Album\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumImage;
use Modules\Album\Services\AlbumMediaService;
use Modules\Album\Transformers\AlbumImageResource;

/**
 * @OA\Tag(
 *     name="Admin Album Images",
 *     description="Upload, reorder, delete album images and set the cover image"
 * )
 */
class AlbumImageController extends Controller
{
    public function __construct(private AlbumMediaService $mediaService) {}

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/images",
     *     summary="Upload one or more images to an album",
     *     tags={"Admin Album Images"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Images uploaded")
     * )
     */
    public function store(Request $request, Album $album)
    {
        $this->authorize('update', $album);

        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:' . config('album.max_image_size_kb', 10240)],
        ]);

        $images = $this->mediaService->uploadImages($album, $request->file('images'));

        return AlbumImageResource::collection(collect($images))
            ->additional(['album' => ['cover_image_id' => $album->fresh()->cover_image_id]])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/albums/{slug}/images/{image}",
     *     summary="Delete an album image",
     *     tags={"Admin Album Images"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="image", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Image deleted")
     * )
     */
    public function destroy(Album $album, AlbumImage $image)
    {
        $this->authorize('update', $album);

        $this->mediaService->deleteImage($album, $image);

        return response()->json(['status' => 'success', 'message' => 'Image deleted.']);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/images/reorder",
     *     summary="Reorder album images",
     *     tags={"Admin Album Images"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ordered_ids"},
     *             @OA\Property(property="ordered_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order updated")
     * )
     */
    public function reorder(Request $request, Album $album)
    {
        $this->authorize('update', $album);

        $data = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:album_images,id'],
        ]);

        $this->mediaService->reorderImages($album, $data['ordered_ids']);

        return AlbumImageResource::collection($album->images()->get());
    }

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/images/{image}/cover",
     *     summary="Set an image as the album's cover image",
     *     tags={"Admin Album Images"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="image", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cover image set")
     * )
     */
    public function setCover(Album $album, AlbumImage $image)
    {
        $this->authorize('update', $album);

        if ($image->album_id !== $album->id) {
            abort(404);
        }

        $album = app(\Modules\Album\Services\AlbumService::class)->setCoverImage($album, $image->id);

        return new AlbumImageResource($album->coverImage);
    }
}
