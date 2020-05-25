<?php

namespace App\Models;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $touches = ['chat'];

    protected $fillable = [
        'user_id', 'chat_id', 'body', 'last_read'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function getBodyAttribute($value)
    {
        if($this->trashed()) {
            if(!auth()->check()) return null;

            return auth()->id() == $this->sender->id ?
                    'このメッセージを削除しました' :
                    "{$this->sender->name}がこのメッセージを削除しました";
        }
        return $value;
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
