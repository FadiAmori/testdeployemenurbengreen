<?php

namespace App\Models\Ai;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChat extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'session_uuid' => 'string',
        'context' => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
