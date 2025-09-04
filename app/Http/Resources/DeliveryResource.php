<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => str_pad($this->id, 6, '0', STR_PAD_LEFT) ,
            'order' => [
                'id'         => $this->order?->id,
            'reference'  => $this->order?->reference,
            'quantity'   => $this->order?->quantity,
            'status'     => $this->order?->status,
            'payment_status' => $this->order?->payment_status,
            'total_ttc'  => $this->order?->total_amount,
            'total'      => $this->order?->total_amount,
            'preparation_time' => $this->order?->preparation_time,
            'customer_name'    => $this->order?->customer?->user?->name ?? '',
            'shipping_address' => $this->order?->deliveryAddress?->label ?? '',
            'shipping_latitude' => $this->order?->deliveryAddress?->latitude,
            'shipping_longitude' => $this->order?->deliveryAddress?->longitude,
            'customer_phone'   => $this->order?->customer?->user?->phone ?? '',
            'store_name'       => $this->order?->store?->name ?? 'Inconnu',
            'store_latitude'   => $this->order?->store?->latitude,
            'store_longitude'  => $this->order?->store?->longitude,
            'store_address'    => $this->order?->store?->address,
            'date'             => $this->order?->created_at?->toDateTimeString(),
            'items'            => $this->order?->orderItems?->map(function ($line) {
        return [
            'id'          => $line->id,
            'name'        => $line->product_name,
            'instructions'=> $line->instructions ?? '',
            'addons'      => is_string($line->addons) ? json_decode($line->addons, true) : $line->addons,
            'ingredients' => is_string($line->ingredients) ? json_decode($line->ingredients, true) : $line->ingredients,
            'quantity'    => $line->quantity,
            'price'       => $line->total_price,
            'total'       => $line->total_price,
        ];
    }) ?? [],
        ],
        'driver' => [
        'id'   => $this->driver_id,
        'name' => $this->driver?->user?->name,
        ],
        'status' => $this->status,
        'current_location' => [
        'latitude' => $this->current_latitude,
        'longitude' => $this->current_longitude,
    ],
        'delivered_at' => $this->delivered_at,
        'created_at'   => $this->created_at->toDateTimeString(),
        'updated_at'   => $this->updated_at->toDateTimeString(),
    ];
}

}
