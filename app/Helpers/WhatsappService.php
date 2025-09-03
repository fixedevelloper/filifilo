<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Http;

class WhatsappService
{

    protected $phoneNumberId;
    protected $tokenID;
    protected $url;

    /**
     * WhatsappService constructor.
     */
    public function __construct()
    {
        $this->phoneNumberId = config('app.WHATSAPP_PHONE_NUMBER_ID');
        $this->tokenID = config('app.WHATSAPP_TOKEN');
        $this->url = "https://graph.facebook.com/v23.0/" .$this->phoneNumberId . "/messages";
    }

    function sendMessage($to, $text)
    {
      return  Http::withToken($this->tokenID)->post($this->url, [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "text",
            "text" => ["body" => $text]
        ]);
    }
     function sendVerificationTemplate($to, $otp)
    {
        $phoneNumberId = config('app.WHATSAPP_PHONE_NUMBER_ID');
        $tokenID = config('app.WHATSAPP_TOKEN');
        $url = "https://graph.facebook.com/v23.0/{$phoneNumberId}/messages";

        $payload = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "template",
            "template" => [
                "name" => "verification_code",
                "language" => ["code" => "fr"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $otp] // {{1}}
                        ]
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $otp // Remplacez par votre lien
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withToken($tokenID)
            ->post($url, $payload);

        return $response;
    }
}
