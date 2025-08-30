<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    use HasFactory;

    protected $fillable = ['recipient_type','recipient_id','order_id','title','message','status','sent_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    protected $appends = ['time_ago'];

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
