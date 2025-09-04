<?php


namespace App\Helpers;

use Google\Auth\OAuth2;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FCMService
{
    public static function send($fcm_id, $title, $body, $data = [])
    {
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = config('services.fcm.key');

        if (!$serverKey) {
            logger("FCM Server Key manquante !");
            return null;
        }

        $payload = [
            "to" => $fcm_id,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
            "data" => $data,
        ];

        $response = Http::withHeaders([
            "Authorization" => "key=$serverKey",
            "Content-Type"  => "application/json",
        ])->post($url, $payload);
        logger("Payload: ".$payload);
        logger("FCM Status: ".$response->status());
        logger("FCM Body: ".$response->body());

        return $response;
    }


    public static  function sendFcm($fcmToken, $title, $body) {
        $serviceAccount = json_decode(file_get_contents(storage_path('app/json/filifilo-customer-firebase-adminsdk-fbsvc-5616d9e510.json')), true);

        $oauth = new OAuth2([
            'audience' => 'https://oauth2.googleapis.com/token',
            'issuer' => $serviceAccount['client_email'],
            'signingAlgorithm' => 'RS256',
            'signingKey' => $serviceAccount['private_key'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ]);

        $token = $oauth->fetchAuthToken();
        $accessToken = $token['access_token'];

        $payload = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
            ]
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post(
            "https://fcm.googleapis.com/v1/projects/{$serviceAccount['project_id']}/messages:send",
            [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]
        );

        return $response->getBody()->getContents();
    }

    static function sendKer($fcmToken, $title, $body){
        $messaging = (new Factory)
            ->withServiceAccount(storage_path('app/json/filifilo-customer-firebase-adminsdk-fbsvc-5616d9e510.json'))
            ->createMessaging();

        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification(['title' => $title, 'body' => $body]);

     return   $messaging->send($message);
    }

}
