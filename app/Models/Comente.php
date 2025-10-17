<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comente extends Model
{
    protected $fillable = ['description', 'statute_id', 'user_id'];

    public function statute()
    {
        return $this->belongsTo(Statute::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
 
