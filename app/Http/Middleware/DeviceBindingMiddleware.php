<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceBindingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        // Only apply to authenticated users (Field Staff primarily)
        if ($user) {
            $deviceId = $request->header('X-Device-ID');

            if (!$deviceId) {
                return response()->json([
                    'message' => 'Device ID header (X-Device-ID) is missing. This application requires device registration.',
                    'error_code' => 'MISSING_DEVICE_ID'
                ], 403);
            }

            // Automatic Binding: If no device is bound yet, bind this one
            if (!$user->device_uuid) {
                $user->device_uuid = $deviceId;
                $user->device_bound_at = now();
                $user->save();
            } 
            // Verification: If a device is already bound, it must match
            elseif ($user->device_uuid !== $deviceId) {
                return response()->json([
                    'message' => 'This account is bound to another device. Please contact administration for a device reset.',
                    'error_code' => 'DEVICE_MISMATCH',
                    'bound_device_hint' => '***' . substr($user->device_uuid, -4)
                ], 403);
            }
        }

        return $next($request);
    }
}
