<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    /**
     * The database connection used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
