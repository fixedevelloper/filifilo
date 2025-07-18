<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'imageUrl', 'store_id', 'category_id'];
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class,'store_id','id');
    }
}
