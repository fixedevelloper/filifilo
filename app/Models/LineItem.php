<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $fillable = [
        'total', 'name', 'quantity',
        'price','order_id'
    ];
}
