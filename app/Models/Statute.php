<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statute extends Model
{
    protected $fillable = ['titre', 'description', 'photo', 'user_id'];

    public function comentes()
    {
        return $this->hasMany(Comente::class);
    }

    public function reactions()
    {
        return $this->hasMany(StatuteReaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likesCount()
    {
        return $this->reactions()->where('reaction', 'like')->count();
    }

    public function dislikesCount()
    {
        return $this->reactions()->where('reaction', 'dislike')->count();
    }
}
 
