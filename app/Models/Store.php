<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name', 'type', 'phone', 'address',
        'latitude', 'longitude', 'time_open', 'time_close',
        'note', 'is_close', 'vendor_id', 'city_id'
    ];
    public function vendor()
    {
        return $this->belongsTo(User::class,'vendor_id','id');
    }
    public function city()
    {
        return $this->belongsTo(City::class,'city_id','id');
    }
}
