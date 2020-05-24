<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'owner_id',
        'slug'
    ];

    protected static function boot()
    {
        parent::boot();

        // teamが作成されたら、現在のユーザーをteam memberとして追加
        static::created(function($team) {
            // auth()->user()->teams()->attach($team->id);
            $team->members()->attach(auth()->id());
        });

        static::deleting(function($team) {
            $team->members()->sync([]);
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
                ->withTimestamps();
    }

    public function designs()
    {
        return $this->hasMany(Design::class);
    }

    public function hasUser(User $user)
    {
        return $this->members()
                ->where('user_id', $user->id)
                ->first() ? true : false;
    }
}
