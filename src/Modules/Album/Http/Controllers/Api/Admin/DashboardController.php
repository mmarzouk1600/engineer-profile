<?php

namespace Modules\Album\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Album\Entities\Album;
use Modules\Album\Services\AlbumService;

/**
 * @OA\Tag(
 *     name="Admin Dashboard",
 *     description="Sales and catalog statistics for the admin dashboard"
 * )
 */
class DashboardController extends Controller
{
    public function __construct(private AlbumService $albumService) {}

    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/stats",
     *     summary="Get dashboard statistics (albums, customers, revenue, payments)",
     *     tags={"Admin Dashboard"},
     *     security={{"jwtAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_albums", type="integer"),
     *             @OA\Property(property="published_albums", type="integer"),
     *             @OA\Property(property="total_customers", type="integer"),
     *             @OA\Property(property="total_purchases", type="integer"),
     *             @OA\Property(property="successful_payments", type="integer"),
     *             @OA\Property(property="pending_payments", type="integer"),
     *             @OA\Property(property="total_revenue", type="number")
     *         )
     *     )
     * )
     */
    public function stats(Request $request)
    {
        $this->authorize('viewAny', Album::class);

        return response()->json($this->albumService->getDashboardStats());
    }
}
