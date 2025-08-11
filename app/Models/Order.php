<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const EN_ATTENTE='EN_ATTENTE';
    const PREPARATION='PREPARATION';
    const EN_LIVRAISON='EN_LIVRAISON';
    const EN_COURS_LIVRAISON='EN_COURS_LIVRAISON';
    const LIVREE='LIVREE';
    const ANNULLEE='ANNULLEE';

    protected $fillable = [
        'total', 'type', 'quantity', 'total_ttc','shipping_address','shipping_longitude','shipping_latitude',
        'status', 'store_id','reference','customer_id','transporter_id'
    ];
    protected $appends = ['time_ago'];

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
    public function store()
    {
        return $this->belongsTo(Store::class,'store_id','id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id','id');
    }
    public function transporter()
    {
        return $this->belongsTo(User::class,'transporter_id','id');
    }
    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
