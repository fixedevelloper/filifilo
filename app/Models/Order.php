<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const PENDING='pending';
    const PREPARATION='preparation';
    const IN_DELIVERY='in_delivery';
    const EN_COURS_LIVRAISON='EN_COURS_LIVRAISON';
    const DELIVERY='delivered';
    const CANCELLED='cancelled';

    use HasFactory;

    protected $fillable = [
        'customer_id','store_id','status','payment_status','reference','payment_method_id',
        'total_amount','preparation_time','delivery_address_id','instructions','discount_amount','final_amount','coupon_id','delivery_time'
    ];

    public function getFinalTotalAttribute()
    {
        $itemsTotal = $this->orderItems->sum->total_price;
        return max(0, $itemsTotal - $this->discount_amount);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function latestDelivery()
    {
        return $this->hasOne(Delivery::class)->latestOfMany();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

}
