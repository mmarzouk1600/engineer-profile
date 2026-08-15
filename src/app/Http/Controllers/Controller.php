<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Customer & Service Management API",
 *     version="1.0.0",
 *     description="API Documentation for Customer and Service Management"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="jwtAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token here without 'Bearer ' prefix"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function responseSuccess($data = [], $msg = '', $statusCode = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $msg
        ], $statusCode);
    }
}
