<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $fillable=[
        'brand','model','color','numberplate','milage','passenger','type','driver_id'
    ];
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
