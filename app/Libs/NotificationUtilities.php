<?php

namespace App\Libs;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationUtilities
{
    public static function sendNotification($token, $notification, $data){
        try {
            $client = new Client();
            $client->setAuthConfig(public_path('/simi-app-production.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $authToken = $client->fetchAccessTokenWithAssertion();

            $message = [
                "token"         => $token,
                "notification"  => $notification,
                "data"          => [
                    'string' => json_encode($data)
                ]
            ];

            Log::channel('firebase')->error($message);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authToken['access_token'],
            ])->post('https://fcm.googleapis.com/v1/projects/simi-project-eb00f/messages:send', [
                'message' => $message
            ]);

            Log::channel('firebase')->info($response->body());
        } catch (\Exception $exception) {
            Log::channel('firebase')->error('EXCEPTION ERROR: '. $exception);
        }
    }
}
