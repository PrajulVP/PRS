<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AuthApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and get JWT token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="superadmin@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $data = $request->all();

        // Host header stripping or Content-Type overriding workaround for live servers
        if (empty($data['email']) && empty($data['password'])) {
            $content = $request->getContent();
            if (!empty($content)) {
                $parsed = json_decode($content, true);
                if (is_array($parsed)) {
                    $data = array_merge($data, $parsed);
                    $request->merge($parsed);
                }
            }
        }

        $validator = Validator::make($data, [
            'email'    => 'required|string',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = [
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => $user,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get authenticated user profile",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data"
     *     )
     * )
     */
    public function profile(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user and invalidate token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
            return response()->json(['message' => 'Token invalidated. Logged out.']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, token invalidation error.'], 500);
        }
    }
}
