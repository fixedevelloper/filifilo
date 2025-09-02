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
    // Ajoutez la méthode toArray() si elle n'existe pas
    public function toArray()
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'recipient_type' => $this->recipient_type,
            'title' => $this->title,
            'recipient_id' => $this->recipient_id,
            'status' => $this->status,
            'message' => $this->message,
            // Inclure d'autres champs ici selon vos besoins
        ];
    }
}
