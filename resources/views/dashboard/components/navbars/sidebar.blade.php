
@props(['activePage'])

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header position-relative px-3 py-2">
        <a href="{{ route('dashboard') }}" class="sidebar-brand text-decoration-none d-block">
            <span class="brand-title">UrbanGreen</span>
            <small class="brand-subtitle">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</small>
        </a>
        <i class="fas fa-times p-3 cursor-pointer opacity-50 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto max-height-vh-100 overflow-y-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ $activePage == 'dashboard' ? ' active bg-gradient-success' : '' }}" href="{{ route('dashboard') }}">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- Users Section --}}
            @php
                $userPages = ['user-profile', 'user-management.index', 'profile'];
                $isUserSection = in_array($activePage, $userPages, true);
            @endphp
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ $isUserSection ? ' active bg-gradient-success' : ' collapsed' }}" href="javascript:;" data-bs-toggle="collapse" data-bs-target="#usersMenu" role="button" aria-expanded="{{ $isUserSection ? 'true' : 'false' }}" aria-controls="usersMenu">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">group</i>
                    </div>
                    <span class="nav-link-text ms-1">Users</span>
                </a>
                <div class="collapse {{ $isUserSection ? 'show' : '' }}" id="usersMenu">
                    <ul class="nav flex-column ms-4">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'user-profile' ? ' active bg-gradient-success' : '' }}" href="{{ route('user-profile') }}">
                                <i class="material-icons opacity-10 me-2">person</i>
                                <span>User Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'user-management.index' ? ' active bg-gradient-success' : '' }}" href="{{ route('user-management.index') }}">
                                <i class="material-icons opacity-10 me-2">manage_accounts</i>
                                <span>User Management</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Shop Section --}}
            @php
                $shopPages = ['ai-report', 'shop', 'shop.categories', 'shop.products', 'shop.orders'];
                $isShopSection = in_array($activePage, $shopPages, true);
            @endphp
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center {{ $isShopSection ? ' active bg-gradient-success' : ' collapsed' }}" href="javascript:;" data-bs-toggle="collapse" data-bs-target="#shopMenu" role="button" aria-expanded="{{ $isShopSection ? 'true' : 'false' }}" aria-controls="shopMenu">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">shopping_cart</i>
                    </div>
                    <span class="nav-link-text ms-1">Shop</span>
                </a>
                <div class="collapse {{ $isShopSection ? 'show' : '' }}" id="shopMenu">
                    <ul class="nav flex-column ms-4">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'ai-report' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.ai-report.index') }}">
                                <i class="material-icons opacity-10 me-2">insights</i>
                                <span>AI Report</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'shop.categories' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.shop.categories') }}">
                                <i class="material-icons opacity-10 me-2">category</i>
                                <span>Catégories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'shop.products' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.shop.products') }}">
                                <i class="material-icons opacity-10 me-2">inventory_2</i>
                                <span>Produits</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ $activePage == 'shop.orders' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.shop.orders') }}">
                                <i class="material-icons opacity-10 me-2">receipt_long</i>
                                <span>Commandes</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Events --}}
            <li class="nav-item">
                <a class="nav-link {{ $activePage == 'event' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.event.index') }}">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">event</i>
                    </div>
                    <span class="nav-link-text ms-1">Event</span>
                </a>
            </li>

            {{-- Blog --}}
            <li class="nav-item">
                <a class="nav-link {{ $activePage == 'blog' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.blog') }}">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">article</i>
                    </div>
                    <span class="nav-link-text ms-1">Blog</span>
                </a>
            </li>

            {{-- Maintenance --}}
            <li class="nav-item">
                <a class="nav-link {{ $activePage == 'maintenance' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.maintenance') }}">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">build</i>
                    </div>
                    <span class="nav-link-text ms-1">Maintenance</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activePage == 'notifications' ? ' active bg-gradient-success' : '' }}" href="{{ route('admin.notifications.index') }}">
                    <div class="text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">notifications</i>
                    </div>
                    <span class="nav-link-text ms-1">Notifications</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
