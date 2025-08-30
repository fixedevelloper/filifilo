<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request) {
        $user=Auth::user();
        $rating=Rating::create([
            'customer_id'=>$user->customer->id,
            'rateable_type'=>$request->rateable_type,
            'rateable_id'=>$request->rateable_id,
            'rating'=>$request->rating,
            'comment'=>$request->comment
        ]);
        return Helpers::success($rating);
    }
    public function update(Request $request, $id) {}
}
