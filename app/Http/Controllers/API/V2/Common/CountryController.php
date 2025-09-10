<?php


namespace App\Http\Controllers\API\V2\Common;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index() {
        $countries=Country::all();
        return Helpers::success($countries);
    }
    public function store(Request $request) {}
}
