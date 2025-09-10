<?php


namespace App\Http\Controllers\API\V2\Admin;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() {}
    public function notifications()
    {
        $user=Auth::user();
        $notifications=Notification::query()->where(['recipient_id'=>$user->id,'recipient_type'=>'admin'])->latest()->get();
        return Helpers::success(NotificationResource::collection($notifications));
    }
}
