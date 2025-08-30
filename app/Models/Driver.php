<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','vehicle_id','current_latitude','current_longitude','device_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

}
