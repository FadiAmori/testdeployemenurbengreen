<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\SubCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::root()
            ->with(['children' => function ($query) {
                $query->withCount('products')->orderBy('position')->orderBy('name');
            }])
            ->withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('position')
            ->paginate($request->integer('per_page', 50));

        if ($request->wantsJson()) {
            return response()->json($categories);
        }

        return view('dashboard.shop.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['name']);
        $position = (int) ($validated['position'] ?? 0);
        $parentId = $validated['parent_id'] ?? null;
        $isSubCategory = ! empty($parentId);

        if ($isSubCategory) {
            $parent = Category::findOrFail($parentId);
            if (SubCategory::where('slug', $validated['slug'])->exists()) {
                throw ValidationException::withMessages([
                    'slug' => 'Ce slug est déjà utilisé par une autre sous-catégorie.',
                ]);
            }
            $subCategory = SubCategory::create([
                'category_id' => $parent->id,
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'position' => $position,
            ]);

            if ($request->wantsJson()) {
                return response()->json($subCategory, 201);
            }

            return back()->with('status', 'Sous-catégorie créée avec succès.');
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        }

        unset($validated['image']);
        $validated['parent_id'] = null;
        $validated['position'] = $position;

        $category = Category::create($validated);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return back()->with('status', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, Category $category): RedirectResponse|Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'parent_id' => ['nullable', 'exists:categories,id', 'not_in:' . $category->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['name']);
        $position = (int) ($validated['position'] ?? 0);
        $parentId = $validated['parent_id'] ?? null;

        if (! empty($parentId)) {
            if ($category->children()->exists()) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Impossible de transformer cette catégorie en sous-catégorie tant qu’elle possède des sous-catégories.',
                ]);
            }

            DB::transaction(function () use ($request, $category, $validated, $parentId, $position) {
                $parent = Category::findOrFail($parentId);

                if (SubCategory::where('slug', $validated['slug'])->exists()) {
                    throw ValidationException::withMessages([
                        'slug' => 'Ce slug est déjà utilisé par une autre sous-catégorie.',
                    ]);
                }

                $subCategory = SubCategory::create([
                    'category_id' => $parent->id,
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'position' => $position,
                ]);

                Product::where('category_id', $category->id)->update([
                    'category_id' => $parent->id,
                    'sub_category_id' => $subCategory->id,
                ]);

                if ($request->hasFile('image') && $category->image_path) {
                    Storage::disk('public')->delete($category->image_path);
                }

                $category->delete();
            });

            if ($request->wantsJson()) {
                return response()->json(['status' => 'converted']);
            }

            return back()->with('status', 'Catégorie convertie en sous-catégorie.');
        }

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('categories', 'public');
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $validated['image_path'] = $newPath;
        }

        unset($validated['image']);
        $validated['parent_id'] = null;
        $validated['position'] = $position;

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json($category);
        }

        return back()->with('status', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse|Response
    {
        $subCategories = $category->children()->withCount('products')->get();

        if ($subCategories->contains(fn ($sub) => $sub->products_count > 0)) {
            return back()->withErrors('Impossible de supprimer cette catégorie : certaines sous-catégories contiennent encore des produits.');
        }

        if ($category->products()->exists()) {
            return back()->withErrors('Impossible de supprimer cette catégorie : elle contient encore des produits.');
        }

        DB::transaction(function () use ($category, $subCategories) {
            foreach ($subCategories as $sub) {
                $sub->delete();
            }

            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return back()->with('status', 'Category deleted successfully.');
    }

    public function updateSubCategory(Request $request, Category $category, SubCategory $subCategory): RedirectResponse|Response
    {
        if ($subCategory->category_id !== $category->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:sub_categories,slug,' . $subCategory->id],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['name']);
        $validated['position'] = (int) ($validated['position'] ?? 0);

        $subCategory->update($validated);

        if ($request->wantsJson()) {
            return response()->json($subCategory);
        }

        return back()->with('status', 'Sous-catégorie mise à jour avec succès.');
    }

    public function destroySubCategory(Request $request, Category $category, SubCategory $subCategory): RedirectResponse|Response
    {
        if ($subCategory->category_id !== $category->id) {
            abort(404);
        }

        if ($subCategory->products()->exists()) {
            return back()->withErrors('Impossible de supprimer cette sous-catégorie : elle contient encore des produits.');
        }

        $subCategory->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return back()->with('status', 'Sous-catégorie supprimée avec succès.');
    }

    public function reorder(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'exists:categories,id'],
            'orders.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['orders'] as $item) {
            Category::whereKey($item['id'])->update(['position' => $item['position']]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'reordered']);
        }

        return back()->with('status', 'Categories reordered successfully.');
    }
}
