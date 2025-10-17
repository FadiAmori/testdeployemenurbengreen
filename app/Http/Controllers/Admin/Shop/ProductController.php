<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'subCategory', 'primaryImage'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%')
                ->orWhere('sku', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($query) => $query->whereIn('status', Arr::wrap($request->input('status'))))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('only_featured'), fn ($query) => $query->where('is_featured', true))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        if ($request->wantsJson()) {
            return response()->json($products);
        }

        return redirect()->route('admin.shop.products');
    }

    public function create()
    {
        return redirect()->route('admin.shop.products');
    }

    public function store(Request $request): RedirectResponse|Response
    {
        [$data, $image] = $this->validatedProductData($request);
        $product = Product::create($data);

        if ($image) {
            $this->storePrimaryImage($product, $image);
        }

        if ($request->wantsJson()) {
            return response()->json($product, 201);
        }

        return redirect()->route('admin.shop.products')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return redirect()->route('admin.shop.products');
    }

    public function update(Request $request, Product $product): RedirectResponse|Response
    {
        [$data, $image] = $this->validatedProductData($request, $product->id);
        $product->update($data);

        if ($image) {
            $this->storePrimaryImage($product, $image, true);
        }

        if ($request->wantsJson()) {
            return response()->json($product);
        }

        return redirect()->route('admin.shop.products')->with('status', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse|Response
    {
        $product->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('admin.shop.products')->with('status', 'Product moved to trash.');
    }

    public function restore(Request $request, int $productId): RedirectResponse|Response
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $product->restore();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'restored']);
        }

        return redirect()->route('admin.shop.products')->with('status', 'Product restored successfully.');
    }

    public function toggleFeatured(Request $request, Product $product): Response|RedirectResponse
    {
        $product->is_featured = ! $product->is_featured;
        $product->save();

        if ($request->wantsJson()) {
            return response()->json(['is_featured' => $product->is_featured]);
        }

        return back()->with('status', 'Product featured flag updated.');
    }

    public function updateInventory(Request $request, Product $product): Response|RedirectResponse
    {
        $validated = $request->validate([
            'adjustment' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $movement = $product->adjustStock($validated['adjustment'], [
            'reason' => $validated['reason'] ?? 'manual',
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => optional($request->user())->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'product' => $product->fresh(),
                'movement' => $movement,
            ]);
        }

        return back()->with('status', 'Inventory updated successfully.');
    }

    protected function validatedProductData(Request $request, ?int $productId = null): array
    {
        $rules = [
            'category_id' => ['nullable', 'exists:categories,id', 'required_without:sub_category_id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'parent_id' => ['nullable', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . ($productId ?? 'NULL')],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku,' . ($productId ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => ['nullable', 'in:draft,scheduled,published,archived'],
            'availability' => ['nullable', 'in:in_stock,limited,out_of_stock,preorder'],
            'unit' => ['nullable', 'string', 'max:50'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'seo' => ['nullable', 'array'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];

        $validated = $request->validate($rules);

        /** @var UploadedFile|null $image */
        $image = $request->file('image');
        unset($validated['image']);

        if (! empty($validated['sub_category_id'])) {
            $subCategory = SubCategory::find($validated['sub_category_id']);

            if (! $subCategory) {
                throw ValidationException::withMessages([
                    'sub_category_id' => 'La sous-catégorie sélectionnée est introuvable.',
                ]);
            }

            if (! empty($validated['category_id']) && (int) $validated['category_id'] !== (int) $subCategory->category_id) {
                throw ValidationException::withMessages([
                    'sub_category_id' => 'La sous-catégorie sélectionnée n’appartient pas à la catégorie choisie.',
                ]);
            }

            $validated['category_id'] = $subCategory->category_id;
        }

        if (empty($validated['category_id'])) {
            throw ValidationException::withMessages([
                'category_id' => 'Veuillez sélectionner au minimum une catégorie ou une sous-catégorie.',
            ]);
        }

        if (! empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        return [$validated, $image];
    }

    protected function storePrimaryImage(Product $product, UploadedFile $file, bool $replace = false): void
    {
        $path = $file->store('products', 'public');

        if ($replace && $product->primaryImage) {
            // Optionally delete previous file
            if ($product->primaryImage->path && Storage::disk('public')->exists($product->primaryImage->path)) {
                Storage::disk('public')->delete($product->primaryImage->path);
            }
            $product->primaryImage->update([
                'path' => $path,
                'alt_text' => $product->name,
            ]);
            return;
        }

        $product->images()->create([
            'path' => $path,
            'alt_text' => $product->name,
            'is_primary' => true,
        ]);
    }
}
