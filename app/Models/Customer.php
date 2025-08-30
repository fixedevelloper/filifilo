<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    // Relation vers le user principal
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Adresses du client
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // Moyens de paiement
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    // Commandes du client
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Points fidélité
    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    // Coupons
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

}
