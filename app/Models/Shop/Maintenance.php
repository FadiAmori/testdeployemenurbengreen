<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'description',
        'steps',
        'photo',
        'video',
        'material_id',
        'optional_id',
    ];

    protected $casts = [
        'steps' => 'array',
    ];

    /**
     * The product that this maintenance belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The product used as material.
     */
    public function material()
    {
        return $this->belongsTo(Product::class, 'material_id');
    }

    /**
     * The product used as optional material.
     */
    public function optional()
    {
        return $this->belongsTo(Product::class, 'optional_id');
    }
}