<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'rateable_type', 'rateable_id', 'rating', 'comment'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rateable()
    {
        return $this->morphTo();
    }
}
