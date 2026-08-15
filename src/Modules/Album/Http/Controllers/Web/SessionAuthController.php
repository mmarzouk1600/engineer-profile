<?php

namespace Modules\Album\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class SessionAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);

        try {
            // ✅ Use JWTAuth facade directly
            $user = JWTAuth::setToken($request->input('token'))->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token.'
                ], 401);
            }

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return response()->json([
                'status' => 'success',
                'user' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token error: ' . $e->getMessage()
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'success']);
    }
}
