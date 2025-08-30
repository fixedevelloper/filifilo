<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id', 'name', 'store_type', 'city_id', 'country_id',
        'latitude','longitude','status','image_url'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
