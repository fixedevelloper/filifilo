<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'reference'  => $this->reference,
            'quantity'   => $this->quantity,
            'status'     => $this->status,
            'payment_status'     => $this->payment_status,
            'total_ttc'  => $this->total_amount,
            'total'      => $this->total_amount,
            'preparation_time'=> $this->preparation_time ?? 0,
            'delivery_time'=>$this->delivery_time,
            'instructions'=> $this->instructions,
            'customer_name'    => $this->customer->user->name ?? '',
            'shipping_address' => $this->deliveryAddress->label ?? '',
            'shipping_latitude' => $this->deliveryAddress->latitude ?? '',
            'shipping_longitude' => $this->deliveryAddress->longitude ?? '',
            'driver'   => [
                'name' =>$this->latestDelivery->driver->user->name ?? '',
                'phone'=>$this->latestDelivery->driver->user->phone ?? ''
            ],
            'driver_vehicule'   => [
                'color'=> $this->latestDelivery->driver->vehicule->color ?? '',
                'brand'=> $this->latestDelivery->driver->vehicule->brand ?? '',
            ],
            'customer_phone'   => $this->customer->phone ?? '',
            'store_name' => $this->store->name ?? 'Inconnu',
            'store_latitude' => $this->store->latitude ?? '4.12',
            'store_longitude' => $this->store->longitude ?? '6.02',
            'store_address' => $this->store->address ?? '6.02',
            'date'       => $this->created_at->toDateTimeString(),
            'items'      => $this->orderItems->map(function ($line) {
                return [
                    'id'       => $line->id,
                    'name'     => $line->product_name,
                    'instructions' => $line->instructions??'',
                    'addons' => $line->addons ? json_decode($line->addons, true) : [],
                    'supplements' => $line->supplements ? json_decode($line->supplements, true) : [],
                    'drinks' => $line->drinks->map(function ($drink) {
                        return [
                            'id' => $drink->id,
                            'name' => $drink->name,
                            'price' => $drink->price,
                            'quantity' => $drink->pivot->quantity,
                        ];
                    }),
                    'ingredients'     => json_decode($line->ingredients),
                    'quantity' => $line->quantity,
                    'price'    => $line->total_price,
                    'total'=>$line->total_price
                ];
            }),
        ];
    }
}
