<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'category_id', 'name', 'description', 'price',
        'stock_quantity','reserved_quantity','stock_alert_level',
        'status','ingredients','addons','is_deliverable','is_pickup','image_url'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'addons' => 'array',
        'is_deliverable' => 'boolean',
        'is_pickup' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

}
