<?php


namespace App\Http\Controllers\API\V2\Common;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request) {
        $countries=City::query()->where('country_id',$request->country_id)->get();
        return Helpers::success($countries);
    }
    public function store(Request $request) {}
}
