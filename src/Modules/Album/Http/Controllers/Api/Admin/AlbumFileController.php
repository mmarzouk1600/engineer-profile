<?php

namespace Modules\Album\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Modules\Album\Services\AlbumMediaService;
use Modules\Album\Transformers\AlbumFileResource;

/**
 * @OA\Tag(
 *     name="Admin Album Files",
 *     description="Upload, reorder and delete downloadable engineering files"
 * )
 */
class AlbumFileController extends Controller
{
    public function __construct(private AlbumMediaService $mediaService) {}

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/files",
     *     summary="Upload one or more engineering files (PDF, DWG, DXF, ZIP, ...)",
     *     tags={"Admin Album Files"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Files uploaded")
     * )
     */
    public function store(Request $request, Album $album)
    {
        $this->authorize('update', $album);

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:' . config('album.max_file_size_kb', 102400),
            ],
        ]);

        $files = $this->mediaService->uploadFiles($album, $request->file('files'));

        return AlbumFileResource::collection(collect($files))->response()->setStatusCode(201);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/albums/{slug}/files/{file}",
     *     summary="Delete an album file",
     *     tags={"Admin Album Files"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="File deleted")
     * )
     */
    public function destroy(Album $album, AlbumFile $file)
    {
        $this->authorize('update', $album);

        $this->mediaService->deleteFile($album, $file);

        return response()->json(['status' => 'success', 'message' => 'File deleted.']);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/albums/{slug}/files/reorder",
     *     summary="Reorder album files",
     *     tags={"Admin Album Files"},
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
            'ordered_ids.*' => ['integer', 'exists:album_files,id'],
        ]);

        $this->mediaService->reorderFiles($album, $data['ordered_ids']);

        return AlbumFileResource::collection($album->files()->get());
    }
}
