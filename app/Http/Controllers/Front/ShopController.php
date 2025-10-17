<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('categories')) {
            $perPage = max(3, min((int) $request->input('per_page', 9), 48));
            $filters = $request->all();
            $filters['per_page'] = $perPage;

            return view('urbangreen.shop', [
                'products' => new LengthAwarePaginator([], 0, $perPage),
                'categories' => collect(),
                'priceRange' => (object) ['min_price' => 0, 'max_price' => 0],
                'filters' => $filters,
            ]);
        }

        $productsQuery = Product::query()
            ->with(['primaryImage', 'subCategory.category'])
            ->active()
            ->when($request->filled('subcategory'), function ($query) use ($request) {
                $value = $request->input('subcategory');
                $subCategory = is_numeric($value)
                    ? SubCategory::find($value)
                    : SubCategory::where('slug', $value)->first();

                if ($subCategory) {
                    $query->where('sub_category_id', $subCategory->id);
                }
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $value = $request->input('category');
                $category = is_numeric($value)
                    ? Category::find($value)
                    : Category::where('slug', $value)->first();

                if ($category) {
                    $ids = $category->subCategories()->pluck('id');
                    if ($ids->isNotEmpty()) {
                        $query->whereIn('sub_category_id', $ids);
                    }
                }
            })
            ->when($request->filled('availability'), fn ($query) => $query->whereIn('availability', Arr::wrap($request->input('availability'))))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('price_min'), fn ($query) => $query->where('price', '>=', $request->float('price_min')))
            ->when($request->filled('price_max'), fn ($query) => $query->where('price', '<=', $request->float('price_max')))
            ->when($request->filled('sort'), function ($query) use ($request) {
                return match ($request->string('sort')) {
                    'price-asc' => $query->orderBy('price'),
                    'price-desc' => $query->orderByDesc('price'),
                    'name-asc' => $query->orderBy('name'),
                    'name-desc' => $query->orderByDesc('name'),
                    'newest' => $query->orderByDesc('published_at'),
                    default => $query->orderByDesc('is_featured')->orderBy('name'),
                };
            }, function ($query) {
                $query->orderByDesc('is_featured')->orderBy('name');
            });

        $perPage = $request->integer('per_page', 9);
        $perPage = max(3, min($perPage, 48));
        $products = $productsQuery->paginate($perPage)->withQueryString();

        $categories = Category::with(['subCategories' => function ($query) {
                $query->with(['category'])->withCount('products')->orderBy('position')->orderBy('name');
            }])
            ->withCount('products')
            ->active()
            ->root()
            ->orderBy('position')
            ->orderBy('name')
            ->get();
        $priceRange = Product::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        $filters = $request->all();
        $filters['per_page'] = $perPage;
        if (isset($filters['category'])) {
            $filters['category'] = (int) $filters['category'];
        }
        if (isset($filters['subcategory'])) {
            $filters['subcategory'] = (int) $filters['subcategory'];
        }

        return view('urbangreen.shop', [
            'products' => $products,
            'categories' => $categories,
            'priceRange' => $priceRange,
            'filters' => $filters,
        ]);
    }

    public function show(Product $product)
    {
        if (! Schema::hasTable('products')) {
            return redirect()->route('front.shop');
        }
        $product->load(['images', 'category', 'primaryImage', 'subCategory']);
        $relatedProducts = Product::active()
            ->where('sub_category_id', $product->sub_category_id)
            ->whereKeyNot($product->id)
            ->limit(6)
            ->get();

        return view('urbangreen.shop-details', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function cart()
    {
        return view('urbangreen.cart');
    }

    public function checkout()
    {
        return view('urbangreen.checkout');
    }

    public function quickView(Product $product): Response
    {
        $product->load(['primaryImage']);

        return response()->json([
            'product' => $product,
        ]);
    }
}
