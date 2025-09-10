<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drink extends Model
{
    protected $fillable=[
        'name','price','store_id'
    ];
    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_drink')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
