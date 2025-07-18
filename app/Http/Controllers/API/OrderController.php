<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $customer = $request->customer;
        $validator=   Validator::make($request->all(),[
            'type' => 'required|string|in:SHOP,STORE', // adapte selon tes types
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }

        Log::info('Début de createOrder', [
            'customer_id' => $customer->id ?? null,
            'email' => $customer->email ?? 'non défini',
        ]);

        $items = $request->items;
        $total = 0.0;

        DB::beginTransaction();

        try {
            // Pré-calcul du total
            foreach ($items as $item) {
                $total += $item['total'];
            }

            $order = Order::create([
                'type' => $request->type,
                'quantity' => count($items),
                'total_ttc' => $total,
                'total' => $total,
                'status' => Helper::STATUSPREPARATION,
                'store_id' => $request->store_id,
                'customer_id' => $customer->id,
                'reference' => 'FF_' . Helper::generateTransactionNumber()
            ]);

            foreach ($items as $item) {
                Log::debug('Création de line item', ['item' => $item]);
                LineItem::create([
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                    'price' => $item['price'],
                    'order_id' => $order->id
                ]);
            }

            DB::commit();

            Log::info('Commande créée avec succès', [
                'order_id' => $order->id,
                'reference' => $order->reference
            ]);

            return Helpers::success([
                'reference' => $order->reference,
                'id' => $order->id,
            ], 'Commande créée avec succès');

        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la commande', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création de la commande.');
        }
    }
    public function orders(Request $request)
    {
        //$storeId = $request->store_id;
        $customer = $request->customer;

        $orders = Order::where('customer_id', $customer->id)->orderByDesc("created_at")->get()->map(function ($order) {
            $lines=LineItem::where('order_id',$order->id)->get()->map(function ($line){
                return [
                    'id'          => $line->id,
                    'name'          => $line->name,
                    'quantity'          => $line->quantity,
                    'price'          => $line->price,
                ];
            });
            return [
                'id'          => $order->id,
                'reference'        => $order->reference,
                'quantity'       => $order->quantity,
                'status'   => $order->status,
                'total_ttc' => $order->total_ttc,
                'total' => $order->total,
                'items'=>$lines,
                'store_name' => $order->store->name,
                'date' => $order->created_at->toDateTimeString(),
            ];
        });

        return Helpers::success($orders, 'Commandes récupérés avec succès');
    }
    public function orderByID(Request $request,$orderId)
    {
        $customer = $request->customer;
        $order = Order::with(['store', 'lineItems'])->find($orderId);

        if (!$order) {
            return Helpers::error('Commande non trouvée');
        }

        return Helpers::success(new OrderResource($order), 'Commande récupérée avec succès');


    }
}
