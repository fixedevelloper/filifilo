<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'total', 'type', 'quantity', 'total_ttc',
        'status', 'store_id','reference','customer_id'
    ];
    public function store()
    {
        return $this->belongsTo(Store::class,'store_id','id');
    }
    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
