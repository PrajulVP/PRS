<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Services\OtpService;

class AuthApiController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
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

        // Device Binding Logic for Field Staff
        if ($user->hasRole('fieldstaff')) {
            $deviceId = $request->header('X-Device-ID');
            if (!$deviceId) {
                return response()->json(['error' => 'Device ID required for Field Staff.'], 403);
            }

            if (!$user->device_uuid) {
                // First login - bind device
                $user->update([
                    'device_uuid' => $deviceId,
                    'device_bound_at' => now(),
                ]);
            } elseif ($user->device_uuid !== $deviceId) {
                return response()->json(['error' => 'This account is bound to another device. Please contact admin.'], 403);
            }
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => $this->prepareUserResponse($user),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/send-otp",
     *     summary="Send OTP to field staff",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="staff@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP sent successfully"),
     *     @OA\Response(response=404, description="User not found or not fieldstaff")
     * )
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized or account not found.'], 404);
        }

        $otp = $this->otpService->generateOtp();
        $this->otpService->sendOtp($user, $otp);

        return response()->json(['message' => 'OTP sent successfully (Check logs for mock OTP).']);
    }

    /**
     * @OA\Post(
     *     path="/api/login-otp",
     *     summary="Login with OTP for field staff",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "otp"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="otp", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid OTP")
     * )
     */
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole('fieldstaff')) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!$this->otpService->verifyOtp($user, $request->otp)) {
            return response()->json(['error' => 'Invalid or expired OTP.'], 401);
        }

        // Generate Token
        if (!$token = auth('api')->fromUser($user)) {
            return response()->json(['error' => 'Failed to generate token.'], 500);
        }

        // Device Binding check
        $deviceId = $request->header('X-Device-ID');
        if (!$deviceId) {
            return response()->json(['error' => 'Device ID required.'], 403);
        }

        if (!$user->device_uuid) {
            $user->update(['device_uuid' => $deviceId, 'device_bound_at' => now()]);
        } elseif ($user->device_uuid !== $deviceId) {
            return response()->json(['error' => 'Account bound to another device.'], 403);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => $this->prepareUserResponse($user),
        ]);
    }

    /**
     * Helper to prepare user response with consistent relationship loading and field hiding.
     */
    private function prepareUserResponse($user)
    {
        if (!$user) return null;

        $user->load(['distributor', 'retailer', 'fieldStaff', 'salesManager']);

        // Hide father's and mother's name for everyone except fieldstaff
        if (!$user->hasRole('fieldstaff')) {
            $user->makeHidden(['fathers_name', 'mothers_name']);
        }

        return $user;
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
        return response()->json($this->prepareUserResponse($user));
    }

    /**
     * @OA\Post(
     *     path="/api/profile/update",
     *     summary="Update authenticated user profile",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="city", type="string", example="New York"),
     *                 @OA\Property(property="pincode", type="string", example="10001"),
     *                 @OA\Property(property="fathers_name", type="string", example="Michael Doe"),
     *                 @OA\Property(property="mothers_name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="shop_name", type="string", description="Only for retailers"),
     *                 @OA\Property(property="gst", type="string", description="For retailers and distributors"),
     *                 @OA\Property(property="drug_license_no", type="string", description="For retailers and distributors"),
     *                 @OA\Property(property="profile_pic", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile updated successfully."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
        ];

        if ($user->hasRole('fieldstaff')) {
            $rules['fathers_name'] = 'nullable|string|max:255';
            $rules['mothers_name'] = 'nullable|string|max:255';
        }

        // Add role-specific validation
        if ($user->hasRole('retailer')) {
            $rules['shop_name'] = 'nullable|string|max:255';
            $rules['gst'] = 'nullable|string|max:50';
            $rules['drug_license_no'] = 'nullable|string|max:50';
        } elseif ($user->hasRole('distributor')) {
            $rules['gst'] = 'nullable|string|max:50';
            $rules['drug_license_no'] = 'nullable|string|max:50';
        }

        if ($user->hasAnyRole(['superadmin', 'admin', 'distributor'])) {
            $rules['contact_no'] = 'nullable|digits:10';
            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                $rules['email'] = 'required|email|unique:users,email,' . $user->id;
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update User Common Fields
        $user->name = $request->name;

        // Only update these fields if they are explicitly sent in the payload
        // This prevents overwriting existing data with nulls if a partial payload is sent
        if ($request->has('address')) $user->address = $request->address;
        if ($request->has('city')) $user->city = $request->city;
        if ($request->has('pincode')) $user->pincode = $request->pincode;
        if ($user->hasRole('fieldstaff')) {
            if ($request->has('fathers_name')) $user->fathers_name = $request->fathers_name;
            if ($request->has('mothers_name')) $user->mothers_name = $request->mothers_name;
        }

        if ($user->hasAnyRole(['superadmin', 'admin', 'distributor'])) {
            if ($request->has('contact_no')) $user->contact_no = $request->contact_no;
            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                if ($request->has('email')) $user->email = $request->email;
            }
        }

        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $user->profile_pic);
            }
            $path = $request->file('profile_pic')->store('profile_pics', 'public');
            $user->profile_pic = $path;
        }

        $user->save();

        // Update Role Specific Fields
        if ($user->hasRole('retailer') && $user->retailer) {
            $retailerData = [];
            if ($request->has('shop_name')) $retailerData['shop_name'] = $request->shop_name;
            if ($request->has('gst')) $retailerData['gst'] = $request->gst;
            if ($request->has('drug_license_no')) $retailerData['drug_license_no'] = $request->drug_license_no;

            if (!empty($retailerData)) $user->retailer->update($retailerData);
        } elseif ($user->hasRole('distributor') && $user->distributor) {
            $distData = [];
            if ($request->has('gst')) $distData['gst'] = $request->gst;
            if ($request->has('drug_license_no')) $distData['drug_license_no'] = $request->drug_license_no;
            if ($request->has('contact_no')) $distData['contact_no'] = $request->contact_no;

            if (!empty($distData)) $user->distributor->update($distData);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->prepareUserResponse($user->fresh())
        ], 200);
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
