<?php


namespace App\Helpers;


use App\Models\Journal;
use Carbon\Carbon;
use http\Exception;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Helper
{

    const STATUSPREPARATION     = 'PREPARATION';
    const STATUSEN_LIVRAISON    = 'EN_LIVRAISON';
    const STATUSLIVREE        = 'LIVREE';
    const STATUSANNULLEE    = 'ANNULLEE';
    const STATUSWAITING     = 5;
    const STATUSFAILD       = 6;
    const STATUSPROCESSING  = 7;
    const TYPESTORESHOP     = "SHOP";
    const TYPESTORERESTAURANT     = "RESTAURANT";
    const METHODBANK    = "BANK";
    const METHODMOBIL     = "MOBIL";
    const OPERATIONDEPOSIT     = "DEPOSIT";
    const OPERATIONWITHDRAW     = "WITHDRAW";
    const OPERATIONTRANSFERT     = "TRANSFER";
    const OPERATIONTRANSFERT_CANCEL     = "TRANSFER_CANCEL";
    const OPERATIONDEPOSIT_CANCEL     = "DEPOSIT_CANCEL";
    const OPERATIONWITHDRAW_CANCEL     = "WITHDRAW_CANCEL";
    const per_page=10;

    static function getDurationOSRM($originLat, $originLng, $destLat, $destLng) {
        $url = "http://router.project-osrm.org/route/v1/driving/$originLng,$originLat;$destLng,$destLat?overview=false";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['routes'][0]['legs'][0]['duration'])) {
            $durationInSeconds = $data['routes'][0]['legs'][0]['duration'];
            // Convertir en heures et minutes
            $hours = floor($durationInSeconds / 3600);
            $minutes = floor(($durationInSeconds % 3600) / 60);
            return "$hours hours $minutes minutes";
        } else {
            return 'Impossible de calculer la durée';
        }
    }


    static function isStoreOpen($time_open, $time_close) {
        // Obtenir l'heure actuelle
        $currentTime = Carbon::now();

        // Vérifier si l'heure d'ouverture est valide
        if (is_null($time_open)) {
            return false;
        }

        // Convertir les horaires d'ouverture et de fermeture en objets Carbon
        $openTime = Carbon::createFromFormat('H:i:s', $time_open);
        $closeTime = Carbon::createFromFormat('H:i:s', $time_close);

        // Si l'heure de fermeture est avant l'heure d'ouverture, cela signifie que l'heure de fermeture est après minuit
        if ($closeTime->lt($openTime)) {
            // Le magasin est ouvert après minuit, donc nous devons vérifier deux conditions:
            // - Soit l'heure actuelle est après l'heure d'ouverture mais avant minuit.
            // - Soit l'heure actuelle est après minuit mais avant l'heure de fermeture du jour suivant.
            if ($currentTime->gte($openTime) || $currentTime->lt($closeTime)) {
                return true;
            }
            return false;
        }

        // Si l'heure de fermeture est après l'heure d'ouverture, vérifier normalement
        return $currentTime->between($openTime, $closeTime);
    }


    public static function  isValidUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        return true;
     /*   try {
            $response = Http::timeout(2)->post($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }*/
    }

    public static function create_journal_deposit($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONDEPOSIT;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance+$amount;
        $journal->save();
    }
    public static function create_journal_Transfer($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONTRANSFERT;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance-$amount;
        $journal->save();
    }
    public static function create_journal_withdraw($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONWITHDRAW;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance-$amount;
        $journal->save();
    }
    public static function create_journal_transfer_cancel($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONTRANSFERT_CANCEL;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance+$amount;
        $journal->save();
    }
    public static function create_journal_deposit_cancel($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONDEPOSIT_CANCEL;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance;
        $journal->save();
    }
    public static function create_journal_withdraw_cancel($amount,$customer_id,$old_balance){
        $journal=new Journal();
        $journal->type_operation=self::OPERATIONWITHDRAW_CANCEL;
        $journal->amount=$amount;
        $journal->customer_id=$customer_id;
        $journal->balance_before=$old_balance;
        $journal->balance_after=$old_balance+$amount;
        $journal->save();
    }
    public static function str_slug($text){
        return strtolower(str_ireplace(" ","_",$text)) ;
    }
    public static function upload(string $dir, string $format, $image = null)
    {
        if ($image != null) {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->put($dir . $imageName, file_get_contents($image));
        } else {
            $imageName = 'def.png';
        }

        return $imageName;
    }
   public static function base64Tofile($dir,$data){
       $image_base64 = base64_decode($data['image']);
       if ($data['image_type'] === 'image/png') {
          $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . '.png';
       }elseif ($data['image_type'] === 'image/jpeg'){
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . '.jpeg';
       } else {
           $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . '.jpg';
       }
       if (!Storage::disk('public')->exists($dir)) {
           Storage::disk('public')->makeDirectory($dir);
       }
       Storage::disk('public')->put($dir . $imageName, $image_base64);
       return $dir.'/'.$imageName;

   }
    public static function generatenumber()
    {
        $tabs=['1','2','3','4','5','6','7','8','9','0'];
        $strong=date("ymds");
        for ($i = 1; $i <= 15; $i++) {
            $strong .= $tabs[rand(0, count($tabs) - 1)];
        }
        return $strong;
    }
    public static function generatApiKey()
    {
        $tabs=['1','2','3','4','5','6','7','8','9','0','q','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l'];
        $strong=date("ymds");
        for ($i = 1; $i <= 26; $i++) {
            $strong .= $tabs[rand(0, count($tabs) - 1)];
        }
        return $strong;
    }
    public static function generateTransactionNumber($size=10)
    {
        $tabs=['1','2','3','4','5','6','7','8','9','0','q','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m'];
        $strong=date("ymds");
        for ($i = 1; $i <= $size; $i++) {
            $strong .= $tabs[rand(0, count($tabs) - 1)];
        }
        return $strong;
    }
    public static function send_creation_account($data)
    {
        $data_ = array('email' => $data['email'],'first_name' => $data['first_name'],'activate_key'=>$data['activate_key'],'slot'=>"");
        Mail::send(['html' => 'mails.verified_mail'], $data_, function ($message)
        use ($data) {
            $message->to($data['email'], $data['first_name'])->subject("Confirm email");
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });

    }
    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $err_keeper[] = ['code' => $index, 'message' => $error[0]];
        }
        return $err_keeper;
    }

    public static function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }
    public static function getAge($date){
        $le=Carbon::createFromFormat("Y-m-d",$date);
        return Carbon::now()->diffInYears($le);
    }

}
