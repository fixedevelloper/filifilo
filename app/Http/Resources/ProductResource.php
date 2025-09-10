<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'store_id'          => $this->store_id,
            'store_name'          => $this->store->name,
            'store_type'          => $this->store->store_type,
            'category_id'       => $this->category_id,
            'category_name'       => $this->category->name,
            'name'              => $this->name,
            'description'       => $this->description,
            'price'             => $this->price,
            'stock_quantity'    => $this->stock_quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'stock_alert_level' => $this->stock_alert_level,
            'status'            => $this->status,
            'ingredients'       => $this->ingredients,
            'addons'            => $this->addons,
            'is_deliverable'    => $this->is_deliverable,
            'is_pickup'         => $this->is_pickup,
            'image_url'         => $this->image_url,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}

