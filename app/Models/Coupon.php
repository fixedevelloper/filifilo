<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id','code','discount_type','discount_value',
        'min_order_amount','status','expiry_date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
