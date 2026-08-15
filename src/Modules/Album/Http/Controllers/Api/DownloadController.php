<?php

namespace Modules\Album\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Tag(
 *     name="Downloads",
 *     description="Protected download of purchased engineering files"
 * )
 */
class DownloadController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/albums/{slug}/files/{file}/download",
     *     summary="Download a purchased engineering file",
     *     description="Requires: authenticated user, a PAID purchase for this album, and the file must belong to the album. Never exposes the physical storage path.",
     *     tags={"Downloads"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="File stream"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden — no paid purchase for this album, or file does not belong to it"),
     *     @OA\Response(response=404, description="File not found")
     * )
     */
    public function download(Request $request, Album $album, AlbumFile $file): StreamedResponse
    {
        // 1. Authenticated — enforced by the "auth:web,api" route middleware.
        // 2 & 3 & 4. Ownership, payment status, and file<->album relationship,
        // all verified inside AlbumFilePolicy::download(). Resolved directly
        // (rather than via Gate's array-based multi-model authorize helper)
        // to keep the policy's (user, album, file) signature unambiguous.
        $allowed = app(\Modules\Album\Policies\AlbumFilePolicy::class)
            ->download($request->user(), $album, $file);

        if (! $allowed) {
            abort(403, 'You do not have access to this file. Please complete your purchase first.');
        }

        $disk = config('album.file_disk', 'local');

        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($file->path)) {
            abort(404, 'File not found.');
        }

        return \Illuminate\Support\Facades\Storage::disk($disk)->download(
            $file->path,
            $file->original_name
        );
    }
}
