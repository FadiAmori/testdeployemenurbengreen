<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Contracts\View\View;
use App\Models\Shop\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function categories(): View
    {
        if (! $this->tablesReady()) {
            return $this->setupView('categories');
        }

        return view('dashboard.pages.shop', [
            'section' => 'categories',
            'tablesReady' => true,
            'stats' => $this->stats(),
            'categories' => $this->categoryTree(),
            'productPaginator' => null,
            'orders' => collect(),
        ]);
    }

    public function products(): View
    {
        if (! $this->tablesReady()) {
            return $this->setupView('products');
        }

        return view('dashboard.pages.shop', [
            'section' => 'products',
            'tablesReady' => true,
            'stats' => $this->stats(),
            'categories' => $this->categoryTree(),
            'productPaginator' => Product::with(['category', 'subCategory', 'primaryImage'])
                ->orderByDesc('created_at')
                ->paginate(10, ['*'], 'products_page'),
            'orders' => collect(),
        ]);
    }

    public function orders(): View
    {
        if (! $this->tablesReady()) {
            return $this->setupView('orders');
        }

        $ordersQuery = Order::with(['user', 'items.product'])
            ->orderByDesc('created_at');

        // simple filters via query string
        if (request()->boolean('pending_only')) {
            $ordersQuery->where('status', 'pending');
        }
        if (request()->string('sort') === 'oldest') {
            $ordersQuery->reorder('created_at');
        }

        $ordersPaginator = $ordersQuery->paginate(10)->appends(request()->query());

        return view('dashboard.pages.shop', [
            'section' => 'orders',
            'tablesReady' => true,
            'stats' => $this->stats(),
            'categories' => $this->categoryTree(),
            'productPaginator' => null,
            'orders' => $ordersPaginator,
        ]);
    }

    protected function tablesReady(): bool
    {
        return Schema::hasTable('categories')
            && Schema::hasTable('products');
    }

    protected function setupView(string $section): View
    {
        return view('dashboard.pages.shop', [
            'section' => $section,
            'tablesReady' => false,
            'stats' => $this->emptyStats(),
            'categories' => collect(),
            'productPaginator' => null,
            'orders' => collect(),
        ]);
    }

    protected function categoryTree(): Collection
    {
        return Category::root()
            ->with(['children' => function ($query) {
                $query->orderBy('position')->withCount('products');
            }])
            ->withCount('products')
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }

    protected function stats(): array
    {
        return [
            'products' => Product::count(),
            'active_products' => Product::active()->count(),
            'categories' => Category::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'stock_threshold')->count(),
        ];
    }

    protected function emptyStats(): array
    {
        return [
            'products' => 0,
            'active_products' => 0,
            'categories' => 0,
            'low_stock' => 0,
        ];
    }
}
