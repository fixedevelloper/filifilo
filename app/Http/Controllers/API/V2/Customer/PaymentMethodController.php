<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function index() {
        $user=Auth::user();
        $method_payments=PaymentMethod::query()->where(['customer_id'=>$user->customer->id])->get();
        return Helpers::success($method_payments);
    }
    public function store(Request $request) {
        $user=Auth::user();
        $validated = $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string',
            'details' => 'required|array',
            'is_default' => 'boolean',
        ]);

      $method=PaymentMethod::create([
          'type' => $validated['type'],
          'details' => $validated['details'],
          'customer_id' => $user->customer->id,
      ]);
        return Helpers::success($method,'Payment method received') ;
    }
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
