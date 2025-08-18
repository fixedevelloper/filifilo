<?php


namespace App\Http\Controllers\API;


use App\Events\NewNotification;
use App\Events\TransporterPositionUpdated;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function send()
    {
        broadcast(new TransporterPositionUpdated([
         'transporterId'=>7,
             'lat'=>4.0511,
             'lng'=>9.7679]));


        broadcast(new NewNotification(
            [
                'user_id' => 4,
                "username" => "Tanbir Ahmed",
                "profile_image" => "https://randomuser.me/api/portraits/men/32.jpg",
                "action_text" => "Placed a new order",
                "time" => "20 min ago",
                "thumbnail_url" => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
            ]
        ));

        return response()->json(['status' => 'Notification envoyée']);
    }

    public function index()
    {
        $notifications=Notification::latest()->get();
        return Helpers::success($notifications);
    }
}
