<?php

namespace Modules\Album\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\Purchase;
use Modules\Album\Transformers\PurchaseResource;

/**
 * @OA\Tag(
 *     name="Admin Purchases",
 *     description="View customers who purchased albums and their payment status"
 * )
 */
class PurchaseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/purchases",
     *     summary="List all purchases with customer, album and payment status",
     *     tags={"Admin Purchases"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="album_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of purchases")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Album::class);

        $query = Purchase::query()->with(['user:id,name,email', 'album:id,title,slug', 'payment'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($albumId = $request->query('album_id')) {
            $query->where('album_id', $albumId);
        }

        return PurchaseResource::collection($query->paginate(20));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/purchases/{uuid}",
     *     summary="Get a single purchase's details",
     *     tags={"Admin Purchases"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Purchase details")
     * )
     */
    public function show(Purchase $purchase)
    {
        $this->authorize('viewAny', Album::class);

        return new PurchaseResource($purchase->load(['user:id,name,email', 'album', 'payment']));
    }
}
