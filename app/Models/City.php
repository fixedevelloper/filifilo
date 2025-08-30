<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name','country_id','postal_code','timezone','default_latitude','default_longitude'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
