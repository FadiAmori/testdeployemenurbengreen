<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatuteReaction extends Model
{
    protected $table = 'statute_reactions';

    protected $fillable = [
        'user_id',
        'statute_id',
        'reaction',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statute()
    {
        return $this->belongsTo(Statute::class);
    }
}
