<?php

namespace Modules\Album\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\Purchase;
use Modules\Album\Services\PurchaseService;
use Modules\Album\Transformers\PurchaseResource;

/**
 * @OA\Tag(
 *     name="Purchases",
 *     description="Customer purchases & Tap Payments checkout"
 * )
 */
class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    /**
     * @OA\Post(
     *     path="/api/albums/{slug}/purchase",
     *     summary="Start (or resume) a purchase for an album and get the Tap payment URL",
     *     description="If the user already owns the album, no new charge is created. If a pending charge exists it is reused/checked instead of creating a duplicate.",
     *     tags={"Purchases"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Already purchased, or payment URL returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request, Album $album)
    {
        if ($album->status->value !== 'published') {
            abort(404);
        }

        $result = $this->purchaseService->initiatePurchase($request->user(), $album);

        if ($result['already_purchased']) {
            return response()->json([
                'status' => 'success',
                'already_purchased' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'already_purchased' => false,
            'payment_url' => $result['payment_url'],
            'purchase' => new PurchaseResource($result['purchase']),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/purchases",
     *     summary="List the authenticated user's purchases",
     *     tags={"Purchases"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Response(response=200, description="Paginated list of purchases")
     * )
     */
    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->with(['album.coverImage'])
            ->latest()
            ->paginate(12);

        return PurchaseResource::collection($purchases);
    }

    /**
     * @OA\Get(
     *     path="/api/purchases/{uuid}",
     *     summary="Get a single purchase belonging to the authenticated user",
     *     tags={"Purchases"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Purchase details"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Request $request, Purchase $purchase)
    {
        if ($purchase->user_id !== $request->user()->id) {
            abort(403);
        }

        return new PurchaseResource($purchase->load(['album.coverImage', 'album.files']));
    }
}
