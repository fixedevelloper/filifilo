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
            'total_ttc'  => $this->total_ttc,
            'total'      => $this->total,
            'customer_name'    => (($this->customer->first_name ?? '') . ' ' . ($this->customer->last_name ?? '')) ?: '',
            'shipping_address' => $this->shipping_address ?? '',
            'shipping_latitude' => $this->shipping_latitude ?? '',
            'shipping_longitude' => $this->shipping_longitude ?? '',
            'customer_phone'   => $this->customer->phone ?? '',
            'store_name' => $this->store->name ?? 'Inconnu',
            'store_latitude' => $this->store->latitude ?? '4.12',
            'store_longitude' => $this->store->longitude ?? '6.02',
            'date'       => $this->created_at->toDateTimeString(),
            'items'      => $this->lineItems->map(function ($line) {
                return [
                    'id'       => $line->id,
                    'name'     => $line->name,
                    'quantity' => $line->quantity,
                    'price'    => $line->price,
                ];
            }),
        ];
    }
}
