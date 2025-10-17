<?php

namespace App\Models\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventoryMovement extends Model
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
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
