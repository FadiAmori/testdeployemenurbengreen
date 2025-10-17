<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mysql'; // Assuming same as Product

    protected $casts = [
        // days stored as JSON array of weekdays e.g. ["monday","wednesday"] or ["everyday"]
        'days' => 'array',
    ];

    public function products()
    {
        $relation = $this->belongsToMany(Product::class);
        try {
            if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'days')) {
                $relation = $relation->withPivot(['days', 'time'])->withTimestamps();
            }
        } catch (\Exception $e) {
            // DB may be unavailable during migrations
        }

        return $relation;
    }
}