<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'username', 'profile_image', 'action_text', 'time', 'thumbnail_url','user_id'
    ];
    protected $appends = ['time_ago'];

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
