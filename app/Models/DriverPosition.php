<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverPosition extends Model
{
    protected $fillable=[
        'driver_id','lat','lng'
    ];
    public function driver()
    {
        return $this->belongsTo(User::class,'driver_id','id');
    }
}
