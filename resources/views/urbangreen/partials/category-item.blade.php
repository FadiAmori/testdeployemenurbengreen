@php
    $isSubCategory = $category instanceof \App\Models\Shop\SubCategory;
    $selectedCategoryId = (int)($filters['category'] ?? 0);
    $query = array_filter(array_merge($filters ?? [], [
        'category' => $isSubCategory ? optional($category->category)->id : $category->id,
        'subcategory' => $isSubCategory ? $category->id : null,
        'page' => null,
    ]), function ($value) {
        return $value !== null && $value !== '';
    });
    $isActive = $isSubCategory
        ? ((int)($filters['subcategory'] ?? null) === $category->id)
        : ($selectedCategoryId === $category->id && empty($filters['subcategory'] ?? null));
    // Only expand children for the selected top-level category
    $expandChildren = (isset($category->subCategories) && $category->subCategories->isNotEmpty())
        && ! $isSubCategory && ($selectedCategoryId === $category->id);
@endphp
<li class="mb-2" style="padding-left: {{ $level * 12 }}px">
    <a class="d-flex justify-content-between align-items-center category-link {{ $isActive ? 'active' : '' }}" href="{{ route('front.shop', $query, false) }}">
        <span class="d-flex align-items-center gap-2">
            @php($showThumb = ! $isSubCategory && ! empty($category->image_path))
            @if($showThumb)
                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width: 28px; height: 28px; object-fit: cover; border-radius: 6px; box-shadow:0 1px 2px rgba(0,0,0,.08); margin-right:8px;">
            @endif
            <span>{{ $category->name }}</span>
        </span>
        <span class="text-muted">({{ $category->products_count }})</span>
    </a>
</li>
@if($expandChildren)
    @foreach($category->subCategories as $child)
        @include('urbangreen.partials.category-item', ['category' => $child, 'filters' => $filters, 'level' => $level + 1])
    @endforeach
@endif
