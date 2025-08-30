<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','currency','default_latitude','default_longitude'];

    public function cities()
    {
        return $this->hasMany(City::class);
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
