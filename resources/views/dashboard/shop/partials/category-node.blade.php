@php
    $indentPx = max($level ?? 0, 0) * 20;
    $collapseId = 'category-edit-' . ($category->id ?? uniqid());
    $productCount = $category->products_count
        ?? ($category->products?->count() ?? 0);
    $isActive = (bool) ($category->is_active ?? true);
    $editingId = (int) old('_editing', 0);
    $isEditingThis = $editingId === (int) ($category->id ?? 0);
    $nameValue = $isEditingThis ? old('name', $category->name) : $category->name;
    $slugValue = $isEditingThis ? old('slug', $category->slug) : $category->slug;
    $parentValue = $isEditingThis ? old('parent_id', $category->parent_id) : $category->parent_id;
    $descriptionValue = $isEditingThis ? old('description', $category->description) : $category->description;
    $isActiveValue = $isEditingThis ? (bool) old('is_active', false) : $isActive;
@endphp

<div class="list-group-item bg-transparent">
    <div class="d-flex flex-column gap-2" style="padding-left: {{ $indentPx }}px;">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($category->image_path)
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 8px;">
                @endif
                <strong>{{ $category->name }}</strong>
                <span class="badge bg-light text-dark">{{ $productCount }} produits</span>
                @unless ($isActive)
                    <span class="badge bg-secondary">Inactif</span>
                @endunless
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                    Modifier
                </button>
                <form method="POST" action="{{ route('admin.shop.categories.destroy', $category) }}" onsubmit="return confirm('Supprimer cette catégorie ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
            </div>
        </div>

        @if (! empty($category->description))
            <p class="text-muted small mb-0">{{ $category->description }}</p>
        @endif

        <div class="collapse" id="{{ $collapseId }}">
            <form method="POST" action="{{ route('admin.shop.categories.update', $category) }}" enctype="multipart/form-data" class="border rounded p-3 bg-light mt-2">
                @csrf
                @method('PUT')
                <input type="hidden" name="_editing" value="{{ $category->id }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Nom</label>
                        <input type="text" name="name" value="{{ $nameValue }}" class="{{ $inputClass }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ $slugValue }}" class="{{ $inputClass }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Catégorie parente</label>
                        <select name="parent_id" class="{{ $inputClass }}">
                            <option value="">Aucune (racine)</option>
                            @foreach($categories as $parentOption)
                                @include('dashboard.shop.partials.category-option', [
                                    'category' => $parentOption,
                                    'level' => 0,
                                    'selected' => $parentValue,
                                    'exclude' => [$category->id],
                                    'fieldName' => 'parent_id',
                                    'useOld' => false,
                                ])
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-2 align-items-end">
                    @if($category->image_path)
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">Image actuelle</label>
                            <div class="border rounded p-2 bg-white d-inline-flex">
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width: 72px; height: 72px; object-fit: cover;">
                            </div>
                        </div>
                    @endif
                    <div class="col-md-{{ $category->image_path ? '8' : '12' }}">
                        <label class="form-label text-muted small mb-1">Image (remplacer)</label>
                        <input type="file" name="image" class="{{ $inputClass }}" accept="image/*">
                        <small class="form-text text-muted">Laisser vide pour conserver l'image actuelle.</small>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label text-muted small mb-1">Description</label>
                    <textarea name="description" rows="2" class="{{ $inputClass }}">{{ $descriptionValue }}</textarea>
                </div>
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="category-active-{{ $category->id }}" name="is_active" value="1" {{ $isActiveValue ? 'checked' : '' }}>
                    <label class="form-check-label" for="category-active-{{ $category->id }}">Active</label>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm">Sauvegarder</button>
                </div>
            </form>
        </div>

        @if(isset($category->children) && $category->children->isNotEmpty())
            <div class="mt-3 pt-3 border-top">
                <p class="text-muted small mb-2 d-flex align-items-center gap-2">
                    <i class="material-icons" style="font-size: 18px;">subdirectory_arrow_right</i>
                    Sous-catégories
                </p>
                <div class="d-flex flex-column gap-2">
                    @foreach($category->children as $child)
                        @php
                            $subCollapseId = 'subcategory-edit-' . $child->id;
                            $isEditingSub = (int) old('_editing_sub', 0) === (int) $child->id;
                            $subName = $isEditingSub ? old('name', $child->name) : $child->name;
                            $subSlug = $isEditingSub ? old('slug', $child->slug) : $child->slug;
                            $subDescription = $isEditingSub ? old('description', $child->description) : $child->description;
                            $subPosition = $isEditingSub ? old('position', $child->position) : $child->position;
                        @endphp
                        <div class="rounded bg-white shadow-sm">
                            <div class="px-3 py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success text-white">Sous-cat.</span>
                                    <span class="fw-semibold">{{ $child->name }}</span>
                                    <span class="badge bg-light text-muted">{{ $child->products_count ?? 0 }} produits</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $subCollapseId }}" aria-expanded="false" aria-controls="{{ $subCollapseId }}">
                                        Modifier
                                    </button>
                                    <form method="POST" action="{{ route('admin.shop.categories.sub.destroy', [$category, $child]) }}" onsubmit="return confirm('Supprimer cette sous-catégorie ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                            <div class="collapse" id="{{ $subCollapseId }}">
                                <form method="POST" action="{{ route('admin.shop.categories.sub.update', [$category, $child]) }}" class="border-top px-3 py-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_editing_sub" value="{{ $child->id }}">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1">Nom</label>
                                            <input type="text" name="name" value="{{ $subName }}" class="{{ $inputClass }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1">Slug</label>
                                            <input type="text" name="slug" value="{{ $subSlug }}" class="{{ $inputClass }}" placeholder="Auto si vide">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-muted small mb-1">Position</label>
                                            <input type="number" name="position" value="{{ $subPosition ?? 0 }}" class="{{ $inputClass }}" min="0">
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="submit" class="btn btn-sm btn-primary">Sauvegarder</button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label text-muted small mb-1">Description</label>
                                        <textarea name="description" rows="2" class="{{ $inputClass }}">{{ $subDescription }}</textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-3 pt-3 border-top">
                <p class="text-muted small mb-0">Aucune sous-catégorie enregistrée pour l'instant.</p>
            </div>
        @endif
    </div>
</div>
