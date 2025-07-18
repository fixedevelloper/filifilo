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
            'store_name' => $this->store->name ?? 'Inconnu',
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
