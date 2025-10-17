@php
    $currentShopSection = $section ?? 'categories';
    $inputClass = 'form-control admin-input';
    $cardClass = 'card admin-card shadow-sm';
@endphp
<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar :activePage="'shop.' . $currentShopSection" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Shop" />
        <div class="container-fluid py-4 shop-admin">

            {{-- Flash Messages & Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(session('status'))
                <div class="alert alert-success" id="flash-success">{{ session('status') }}</div>
                @push('js')
                <script>
                    setTimeout(function () {
                        var el = document.getElementById('flash-success');
                        if (el) el.remove();
                    }, 5000);
                </script>
                @endpush
            @endif

            {{-- Statistics Cards --}}
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
                <div class="col">
                    <div class="{{ $cardClass }} h-100 stat-card">
                        <div class="card-body d-flex flex-column justify-content-center text-center py-4">
                            <h6 class="text-uppercase text-secondary mb-2">Products</h6>
                            <h3 class="mb-1 fw-bold">{{ $stats['products'] ?? 0 }}</h3>
                            <small class="text-success">{{ $stats['active_products'] ?? 0 }} active</small>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="{{ $cardClass }} h-100 stat-card">
                        <div class="card-body d-flex flex-column justify-content-center text-center py-4">
                            <h6 class="text-uppercase text-secondary mb-2">Categories</h6>
                            <h3 class="mb-1 fw-bold">{{ $stats['categories'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="{{ $cardClass }} h-100 stat-card">
                        <div class="card-body d-flex flex-column justify-content-center text-center py-4">
                            <h6 class="text-uppercase text-secondary mb-2">Inventory alerts</h6>
                            <h3 class="mb-1 fw-bold">{{ $stats['low_stock'] ?? 0 }}</h3>
                            <small class="text-danger">Stock ≤ threshold</small>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="admin-section-title mb-3">Section: {{ ucfirst($currentShopSection) }}</h5>

            {{-- Migration Warning --}}
            @if(!($tablesReady ?? false))
                <div class="alert alert-warning">
                    <strong>Shop setup required.</strong> Run the migrations to create shop tables:<br>
                    <code>php artisan migrate</code>
                </div>
            @endif

            @if($tablesReady ?? false)
                @switch($currentShopSection)

                    {{-- ==================== CATEGORIES SECTION ==================== --}}
                    @case('categories')
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="{{ $cardClass }} mb-4">
                                    <div class="card-header bg-transparent border-0 pb-0">
                                        <h6 class="mb-0">Ajouter une catégorie</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('admin.shop.categories.store') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="mt-3">
                                                <label class="form-control-label">Nom</label>
                                                <input type="text" name="name" class="{{ $inputClass }}" required>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-control-label">Catégorie parente</label>
                                                <select name="parent_id" class="{{ $inputClass }}">
                                                    <option value="">Aucune (racine)</option>
                                                    @if(isset($categories) && $categories->isNotEmpty())
                                                        @foreach($categories as $category)
                                                            @include('dashboard.shop.partials.category-option', [
                                                                'category' => $category,
                                                                'level' => 0,
                                                                'fieldName' => 'category_id',
                                                            ])
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-control-label">Description</label>
                                                <textarea name="description" class="{{ $inputClass }}" rows="2"></textarea>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-control-label">Image (optionnelle)</label>
                                                <input type="file" name="image" class="{{ $inputClass }}" accept="image/*">
                                                <small class="form-text text-muted">Formats supportés: JPG, PNG, max 2 Mo.</small>
                                            </div>
                                            <div class="form-group form-check">
                                                <input type="checkbox" class="form-check-input" id="category-active" name="is_active" value="1" checked>
                                                <label class="form-check-label" for="category-active">Active</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Créer la catégorie</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="{{ $cardClass }}">
                                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Catégories &amp; sous-catégories</h6>
                                        <small class="text-muted">Cliquer pour modifier</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @php
                                                $allCategories = $categories ?? collect();
                                            @endphp
                                            @if($allCategories->isEmpty())
                                                <div class="list-group-item bg-transparent text-muted">Aucune catégorie pour le moment.</div>
                                            @else
                                                @foreach($allCategories as $category)
                                                    @include('dashboard.shop.partials.category-node', [
                                                        'category' => $category,
                                                        'categories' => $categories,
                                                        'level' => 0,
                                                        'inputClass' => $inputClass
                                                    ])
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @break

                    {{-- ==================== PRODUCTS SECTION ==================== --}}
                    @case('products')
                        <div class="{{ $cardClass }} mb-4">
                            <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                <div>
                                    <h6 class="text-uppercase text-secondary mb-1">Ajouter un contenu</h6>
                                    <p class="mb-0 text-muted small">Choisissez le type d'élément à créer dans la boutique.</p>
                                </div>
                                <div class="btn-group" role="group" aria-label="Ajouter un contenu">
                                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCreateProduct" aria-expanded="false" aria-controls="collapseCreateProduct">
                                        Nouveau produit
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Create Product Form --}}
                        <div class="collapse" id="collapseCreateProduct">
                            <div class="{{ $cardClass }} mb-4">
                                <div class="card-header bg-transparent border-0 pb-0">
                                    <h6 class="mb-0">Créer un produit</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.shop.products.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Nom</label>
                                                <input type="text" name="name" class="{{ $inputClass }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">SKU</label>
                                                <input type="text" name="sku" class="{{ $inputClass }}" placeholder="Auto si vide">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Image principale</label>
                                                <input type="file" name="image" class="{{ $inputClass }}" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-4">
                                                <label class="form-label text-muted">Catégorie</label>
                                                <select name="category_id" class="{{ $inputClass }}" required>
                                                    <option value="">Sélectionner</option>
                                                    @if(isset($categories) && $categories->isNotEmpty())
                                                        @foreach($categories as $category)
                                                            @include('dashboard.shop.partials.category-option', [
                                                                'category' => $category,
                                                                'level' => 0,
                                                                'fieldName' => 'category_id',
                                                            ])
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted">Sous-catégorie</label>
                                                <select name="sub_category_id" class="{{ $inputClass }}">
                                                    @include('dashboard.shop.partials.sub-category-options', [
                                                        'categories' => $categories ?? collect(),
                                                        'selected' => old('sub_category_id')
                                                    ])
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-muted">Prix</label>
                                                <input type="number" step="0.01" min="0" name="price" class="{{ $inputClass }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-muted">Prix promo</label>
                                                <input type="number" step="0.01" min="0" name="sale_price" class="{{ $inputClass }}">
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Stock</label>
                                                <input type="number" min="0" name="stock" class="{{ $inputClass }}" value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Seuil d'alerte</label>
                                                <input type="number" min="0" name="stock_threshold" class="{{ $inputClass }}" value="5">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Statut</label>
                                                <select name="status" class="{{ $inputClass }}">
                                                    <option value="draft">Brouillon</option>
                                                    <option value="scheduled">Planifié</option>
                                                    <option value="published">Publié</option>
                                                    <option value="archived">Archivé</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Disponibilité</label>
                                                <select name="availability" class="{{ $inputClass }}">
                                                    <option value="in_stock">En stock</option>
                                                    <option value="limited">Limité</option>
                                                    <option value="out_of_stock">Rupture</option>
                                                    <option value="preorder">Précommande</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label text-muted">Description courte</label>
                                            <textarea name="short_description" class="{{ $inputClass }}" rows="2"></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label text-muted">Description</label>
                                            <textarea name="description" class="{{ $inputClass }}" rows="4"></textarea>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch text-start ps-0">
                                                    <input type="checkbox" class="form-check-input ms-2" id="product-active" name="is_active" value="1" checked>
                                                    <label class="form-check-label ms-5" for="product-active">Afficher sur la boutique</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch text-start ps-0">
                                                    <input type="checkbox" class="form-check-input ms-2" id="product-featured" name="is_featured" value="1">
                                                    <label class="form-check-label ms-5" for="product-featured">Mettre en vedette</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary">Enregistrer le produit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Products Listing --}}
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="{{ $cardClass }} card-admin">
                                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 card-title">Produits</h6>
                                        <small class="text-muted">{{ $productPaginator?->total() ?? 0 }} au total</small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table-admin align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width:80px">Image</th>
                                                    <th>Produit</th>
                                                    <th>Catégorie</th>
                                                    <th>Statut</th>
                                                    <th>Prix</th>
                                                    <th>Stock</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $allProducts = $productPaginator ?? collect();
                                                @endphp
                                                @if($allProducts && $allProducts->count())
                                                    @foreach($allProducts as $product)
                                                        @php
                                                            $statusKey = strtolower($product->status);
                                                            $statusLabel = strtoupper($product->status);
                                                            $statusClasses = [
                                                                'published' => 'pill pill--active',
                                                                'active' => 'pill pill--active',
                                                                'scheduled' => 'pill pill--scheduled',
                                                                'archived' => 'pill pill--danger',
                                                                'draft' => 'pill',
                                                            ];
                                                            $statusClass = $statusClasses[$statusKey] ?? 'pill';
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="product-thumb">
                                                            </td>
                                                            <td>
                                                                <strong>{{ $product->name }}</strong><br>
                                                                <small class="text-muted">SKU: {{ $product->sku }}</small>
                                                            </td>
                                                            <td>
                                                                {{ optional($product->category)->name ?? '—' }}
                                                                @if($product->subCategory)
                                                                    <span class="d-block text-muted small">Sous-cat.: {{ $product->subCategory->name }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                                                @if($product->is_featured)
                                                                    <span class="pill pill--scheduled">Vedette</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                ${{ number_format($product->sale_price ?? $product->price, 2) }}
                                                                @if($product->sale_price && $product->sale_price < $product->price)
                                                                    <small class="text-muted d-block"><del>${{ number_format($product->price, 2) }}</del></small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $product->stock }}
                                                                @if($product->stock <= $product->stock_threshold)
                                                                    <span class="pill pill--danger">Low</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="table-actions">
                                                                    <form method="POST" action="{{ route('admin.shop.products.toggle-featured', $product) }}">
                                                                        @csrf
                                                                        <button type="submit" class="btn-icon" aria-label="{{ $product->is_featured ? 'Retirer de la sélection' : 'Mettre en vedette' }}">
                                                                            <i class="material-icons" aria-hidden="true">star</i>
                                                                        </button>
                                                                    </form>
                                                                    <button class="btn-icon" type="button" data-bs-toggle="collapse" data-bs-target="#product-edit-{{ $product->id }}" aria-expanded="false" aria-controls="product-edit-{{ $product->id }}" aria-label="Modifier">
                                                                        <i class="material-icons" aria-hidden="true">edit</i>
                                                                    </button>
                                                                    <form method="POST" action="{{ route('admin.shop.products.destroy', $product) }}" onsubmit="return confirm('Archiver ce produit ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn-icon btn-danger" aria-label="Supprimer">
                                                                            <i class="material-icons" aria-hidden="true">delete</i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="collapse" id="product-edit-{{ $product->id }}">
                                                            <td colspan="7">
                                                                <form method="POST" action="{{ route('admin.shop.products.update', $product) }}" enctype="multipart/form-data">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="row g-3">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Nom</label>
                                                                            <input type="text" name="name" class="{{ $inputClass }}" value="{{ $product->name }}" required>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Prix</label>
                                                                            <input type="number" step="0.01" name="price" class="{{ $inputClass }}" value="{{ $product->price }}" required>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Prix promo</label>
                                                                            <input type="number" step="0.01" name="sale_price" class="{{ $inputClass }}" value="{{ $product->sale_price }}">
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Sous-catégorie</label>
                                                                            <select name="sub_category_id" class="{{ $inputClass }}">
                                                                                @include('dashboard.shop.partials.sub-category-options', [
                                                                                    'categories' => $categories ?? collect(),
                                                                                    'selected' => $product->sub_category_id,
                                                                                ])
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row g-3 mt-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Catégorie</label>
                                                                            <select name="category_id" class="{{ $inputClass }}" required>
                                                                                @if(isset($categories) && $categories->isNotEmpty())
                                                                                    @foreach($categories as $category)
                                                                                        @include('dashboard.shop.partials.category-option', [
                                                                                            'category' => $category,
                                                                                            'level' => 0,
                                                                                            'selected' => $product->category_id,
                                                                                            'fieldName' => 'category_id',
                                                                                            'useOld' => false,
                                                                                        ])
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Statut</label>
                                                                            <select name="status" class="{{ $inputClass }}">
                                                                                <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                                                                                <option value="scheduled" {{ $product->status === 'scheduled' ? 'selected' : '' }}>Planifié</option>
                                                                                <option value="published" {{ $product->status === 'published' ? 'selected' : '' }}>Publié</option>
                                                                                <option value="archived" {{ $product->status === 'archived' ? 'selected' : '' }}>Archivé</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Disponibilité</label>
                                                                            <select name="availability" class="{{ $inputClass }}">
                                                                                <option value="in_stock" {{ $product->availability === 'in_stock' ? 'selected' : '' }}>En stock</option>
                                                                                <option value="limited" {{ $product->availability === 'limited' ? 'selected' : '' }}>Limité</option>
                                                                                <option value="out_of_stock" {{ $product->availability === 'out_of_stock' ? 'selected' : '' }}>Rupture</option>
                                                                                <option value="preorder" {{ $product->availability === 'preorder' ? 'selected' : '' }}>Précommande</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Slug</label>
                                                                            <input type="text" name="slug" class="{{ $inputClass }}" value="{{ $product->slug }}" placeholder="Auto si vide">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row g-3 mt-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Actif</label>
                                                                            <select name="is_active" class="{{ $inputClass }}">
                                                                                <option value="1" {{ $product->is_active ? 'selected' : '' }}>Oui</option>
                                                                                <option value="0" {{ ! $product->is_active ? 'selected' : '' }}>Non</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Vedette</label>
                                                                            <select name="is_featured" class="{{ $inputClass }}">
                                                                                <option value="1" {{ $product->is_featured ? 'selected' : '' }}>Oui</option>
                                                                                <option value="0" {{ ! $product->is_featured ? 'selected' : '' }}>Non</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Image (remplacer)</label>
                                                                            <input type="file" name="image" class="{{ $inputClass }}" accept="image/*">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <label class="form-label text-muted">Description courte</label>
                                                                        <textarea name="short_description" class="{{ $inputClass }}" rows="2">{{ $product->short_description }}</textarea>
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <label class="form-label text-muted">Description</label>
                                                                        <textarea name="description" class="{{ $inputClass }}" rows="4">{{ $product->description }}</textarea>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end mt-4">
                                                                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                                                                    </div>
                                                                </form>

                                                                <hr class="my-3">
                                                                <form method="POST" action="{{ route('admin.shop.products.inventory', $product) }}">
                                                                    @csrf
                                                                    <div class="row g-3 align-items-end">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label text-muted">Ajuster le stock</label>
                                                                            <input type="number" name="adjustment" class="{{ $inputClass }}" placeholder="± qty" required>
                                                                        </div>
                                                                        <div class="col-md-5">
                                                                            <label class="form-label text-muted">Motif (optionnel)</label>
                                                                            <input type="text" name="reason" class="{{ $inputClass }}" placeholder="Motif (optionnel)">
                                                                        </div>
                                                                        <div class="col-md-4 d-flex justify-content-end">
                                                                            <button type="submit" class="btn btn-outline mt-3 mt-md-0">Mettre à jour</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">Aucun produit créé pour l'instant.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    @if($productPaginator)
                                        <div class="card-footer bg-transparent border-0">
                                            {{ $productPaginator->links('pagination::bootstrap-4') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @break

                    {{-- ==================== ORDERS SECTION ==================== --}}
                    @case('orders')
                        <div class="row">
                            <div class="col-12">
                                <div class="{{ $cardClass }}">
                                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Commandes clients</h6>
                                        <form method="GET" action="{{ route('admin.shop.orders') }}" class="d-flex gap-2 align-items-center">
                                            <label class="me-2 text-muted">Trier:</label>
                                            <select name="sort" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                                                <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>Plus récentes</option>
                                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Plus anciennes</option>
                                            </select>
                                            <div class="form-check ms-3">
                                                <input type="checkbox" name="pending_only" value="1" class="form-check-input" id="pendingOnly" onchange="this.form.submit()" {{ request('pending_only') ? 'checked' : '' }}>
                                                <label for="pendingOnly" class="form-check-label">En attente seulement</label>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table-admin align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Commande</th>
                                                    <th>Client</th>
                                                    <th>Produits</th>
                                                    <th>Total</th>
                                                    <th>Statut</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(($orders?->count() ?? 0) > 0)
                                                    @foreach($orders as $order)
                                                        <tr>
                                                            <td>
                                                                <strong>#{{ $order->id }}</strong><br>
                                                                <small class="text-muted">{{ $order->created_at->format('Y-m-d H:i') }}</small>
                                                            </td>
                                                            <td>
                                                                {{ $order->user->prenom ?? '' }} {{ $order->user->name ?? '' }}<br>
                                                                <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                                            </td>
                                                            <td>
                                                                @foreach($order->items as $item)
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <img src="{{ $item->product->primary_image_url }}" alt="" class="product-thumb" style="width:32px;height:32px;">
                                                                        <small>{{ $item->product->name }} × {{ $item->quantity }}</small>
                                                                    </div>
                                                                @endforeach
                                                            </td>
                                                            <td>${{ number_format($order->total_price, 2) }}</td>
                                                            <td>
                                                                <span class="pill {{ $order->status === 'pending' ? 'pill--scheduled' : 'pill--active' }}">{{ ucfirst($order->status) }}</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="table-actions">
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#adminOrderDetails{{ $order->id }}">Détails</button>
                                                                    @if($order->status === 'pending')
                                                                        <form method="POST" action="{{ route('admin.shop.orders.confirm', $order) }}" onsubmit="return confirm('Confirmer cette commande ?');">
                                                                            @csrf
                                                                            <button class="btn btn-success btn-sm">Confirmer</button>
                                                                        </form>
                                                                    @endif
                                                                    <form method="POST" action="{{ route('admin.shop.orders.destroy', $order) }}" onsubmit="return confirm('Supprimer cette commande ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="btn btn-danger btn-sm">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <!-- Admin Order Details Modal -->
                                                        <div class="modal fade" id="adminOrderDetails{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Commande #{{ $order->id }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row g-3">
                                                                            <div class="col-md-6">
                                                                                <h6 class="mb-2">Client</h6>
                                                                                <p class="mb-1">{{ $order->user->prenom ?? '' }} {{ $order->user->name ?? '' }}</p>
                                                                                <p class="text-muted mb-3">{{ $order->user->email ?? '' }}</p>
                                                                                <h6 class="mb-2">Adresse de livraison</h6>
                                                                                <p class="mb-0">{{ $order->shipping_address }}</p>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <h6 class="mb-2">Récapitulatif</h6>
                                                                                <div class="d-flex justify-content-between"><span>Date</span><strong>{{ $order->created_at->format('Y-m-d H:i') }}</strong></div>
                                                                                <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format($order->total_price, 2) }}</strong></div>
                                                                                <div class="d-flex justify-content-between"><span>Statut</span><strong>{{ ucfirst($order->status) }}</strong></div>
                                                                            </div>
                                                                        </div>
                                                                        <hr>
                                                                        <h6 class="mb-2">Produits</h6>
                                                                        <ul class="list-unstyled mb-0">
                                                                            @foreach($order->items as $item)
                                                                                <li class="d-flex align-items-center mb-2">
                                                                                    <img src="{{ $item->product->primary_image_url }}" class="product-thumb me-2" style="width:40px;height:40px;">
                                                                                    <span class="me-auto">{{ $item->product->name }} × {{ $item->quantity }}</span>
                                                                                    <strong>${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</strong>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <tr><td colspan="6" class="text-center text-muted">Aucune commande trouvée.</td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    @if(method_exists($orders, 'links'))
                                        <div class="card-footer bg-transparent border-0">
                                            {{ $orders->links('pagination::bootstrap-4') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @break

                    @default
                        {{-- Unknown section: show nothing --}}
                @endswitch
            @endif
        </div>
    </main>
</x-dashboard::layout>
