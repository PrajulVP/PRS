<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait OneSignalNotifications
{
    /**
     * Send a push notification via OneSignal.
     *
     * @param array $userIds Database User IDs (External IDs in OneSignal)
     * @param string $message The notification message
     * @param array $data Additional data to send
     * @param string $title Optional title
     * @return bool
     */
    public function sendOneSignalPush(array $userIds, string $message, array $data = [], string $title = 'PRS Notification')
    {
        $appId = config('services.onesignal.app_id');
        $restApiKey = config('services.onesignal.rest_api_key');

        if (!$appId || !$restApiKey) {
            Log::error('OneSignal configuration missing.');
            return false;
        }

        // Fetch player_ids from database for these users
        $playerIds = \App\Models\User::whereIn('id', $userIds)
            ->whereNotNull('player_id')
            ->pluck('player_id')
            ->toArray();

        $payload = [
            'app_id' => $appId,
            'include_external_user_ids' => array_map('strval', $userIds),
            'contents' => ['en' => $message],
            'headings' => ['en' => $title],
            'data' => $data,
        ];

        if (!empty($playerIds)) {
            $payload['include_player_ids'] = $playerIds;
        }

        try {
            Log::info('OneSignal Push Request', [
                'target_user_ids' => $userIds,
                'found_player_ids_count' => count($playerIds),
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $restApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            $responseBody = $response->json();

            if ($response->successful()) {
                Log::info('OneSignal Push Success', [
                    'response' => $responseBody
                ]);
                return true;
            }

            Log::error('OneSignal Push Failed', [
                'status' => $response->status(),
                'response' => $responseBody ?? $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('OneSignal Notification Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
