<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'=>$this->title,
            'message'=>$this->message,
            'status'=>$this->status,
            'sent_at'=>$this->sent_at,

                'order_id'=>$this->order_id,
                'order_total'=>$this->order->total_amount,


                'customer_id'=>$this->order->customer->id,
                'customer_name'=>$this->order->customer->user->name,
                'customer_phone'=>$this->order->customer->user->phone,


                'merchant_id'=>$this->order->store->merchant->id,
                'merchant_name'=>$this->order->store->merchant->user->name,
                'merchant_phone'=>$this->order->store->merchant->user->phone,
                'store_id'=>$this->order->store->id,
                'store_name'=>$this->order->store->name,
        ];
    }
}
