<?php

namespace Database\Factories\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'sku' => strtoupper(Str::random(8)),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'sale_price' => null,
            'stock' => $this->faker->numberBetween(5, 20),
            'stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => false,
            'status' => 'published',
            'availability' => 'in_stock',
            'unit' => 'pcs',
            'weight' => $this->faker->randomFloat(2, 0.2, 2),
            'dimensions' => null,
            'attributes' => [],
            'seo' => null,
            'published_at' => now(),
        ];
    }
}
