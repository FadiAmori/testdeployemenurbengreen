<?php
// routes/web.php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front\ShopController;
use App\Http\Controllers\Front\UrbanGreenController;
use App\Http\Controllers\Admin\Shop\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\Shop\DashboardController as AdminShopDashboardController;
use App\Http\Controllers\Admin\Shop\OrderAdminController as AdminOrderController;
use App\Http\Controllers\Admin\Shop\ProductController as AdminProductController;
use App\Http\Controllers\Admin\Maintenance\MaintenanceController;
use App\Http\Controllers\Admin\Maintenance\NotificationController; // Import for standalone Notifications
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ComenteController;
use App\Http\Controllers\StatuteController;
use App\Http\Controllers\StatuteReactionController;
use App\Http\Controllers\Admin\Event\EventController;
use App\Http\Controllers\ShopAiChatController;
use App\Http\Controllers\Admin\AiReportController;

// Helper to stream assets stored under resources/front/*
$serveResourceAsset = function (string $relativeBase) {
    return function (string $path) use ($relativeBase) {
        $basePath = realpath(resource_path($relativeBase));
        $targetPath = realpath(resource_path($relativeBase . '/' . $path));

        if ($basePath === false || $targetPath === false || strpos($targetPath, $basePath) !== 0 || !File::exists($targetPath)) {
            abort(404);
        }

        $mimeType = File::mimeType($targetPath);
        if ($mimeType === 'text/plain') {
            $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
            $overrides = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'map' => 'application/json',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'otf' => 'font/otf',
                'eot' => 'application/vnd.ms-fontobject',
            ];
            if (isset($overrides[$extension])) {
                $mimeType = $overrides[$extension];
            }
        }

        return response(File::get($targetPath), 200, ['Content-Type' => $mimeType]);
    };
};

// Serve files from storage/app/public when storage:link is not available
Route::get('/storage/{path}', function (string $path) {
    $basePath = realpath(storage_path('app/public'));
    $targetPath = realpath(storage_path('app/public/' . $path));

    if ($basePath === false || $targetPath === false || strpos($targetPath, $basePath) !== 0 || !File::exists($targetPath)) {
        abort(404);
    }

    $mimeType = File::mimeType($targetPath) ?: 'application/octet-stream';
    return response(File::get($targetPath), 200, ['Content-Type' => $mimeType]);
})->where('path', '.*');

// Client-facing assets (UrbanGreen front)
Route::get('/urbangreen/{path}', $serveResourceAsset('front/client'))->where('path', '.*');

// Admin dashboard assets (Material Dashboard front)
Route::get('/assets/{path}', $serveResourceAsset('front/admin'))->where('path', '.*');

// User front (UrbanGreen) served from Blade views
Route::controller(UrbanGreenController::class)->group(function () {
    Route::get('/', 'home')->name('front.home');
    Route::get('/event', 'event')->name('front.event');
    // Views handled by CartController; keep fallbacks here to avoid 404 before auth
    // Overridden by explicit routes below.
    Route::get('/cart', 'cart')->name('front.cart.placeholder');
    Route::get('/checkout', 'checkout')->name('front.checkout.placeholder');
    Route::get('/portfolio', 'portfolio')->name('front.portfolio');
    Route::get('/portfolio/single', 'singlePortfolio')->name('front.portfolio.single');
    Route::get('/blog', 'blog')->name('front.blog');
    Route::get('/blog/post', 'singlePost')->name('front.blog.single');
    Route::get('/maintenance', 'maintenance')->name('front.maintenance');
    // Event details and enroll
    Route::get('/event/{event}', 'showEvent')->name('front.event.show');
    Route::post('/event/{event}/enroll', 'enrollEvent')->name('front.event.enroll')->middleware('auth');
    // Confirm attendance (user self-confirm) - matches UrbanGreenController::confirmAttendance
    Route::post('/event/{event}/confirm', 'confirmAttendance')->name('front.event.confirm')->middleware('auth');
    // Dataset event (from ML recommendations) - for demo
    Route::get('/dataset-event/{eventId}', 'showDatasetEvent')->name('front.dataset.event.show');
});

// New: JSON endpoint for front event calendar data
Route::get('/event/calendar-data', [\App\Http\Controllers\Admin\Event\EventController::class, 'frontCalendarData'])->name('front.event.calendar-data');

Route::controller(ShopController::class)->group(function () {
    Route::get('/shop', 'index')->name('front.shop');
    Route::get('/shop/details', fn () => redirect()->to(route('front.shop', [], false)))->name('front.shop.details');
    Route::get('/shop/quick-view/{product}', 'quickView')->name('front.shop.quick-view');
    Route::get('/shop/{product}', 'show')->name('front.shop.show');
});

Route::get('/shop/ai-chat/history', [ShopAiChatController::class, 'history'])->name('front.shop.ai-chat.history');
Route::post('/shop/ai-chat/message', [ShopAiChatController::class, 'message'])->name('front.shop.ai-chat.message')->middleware('throttle:20,1');
Route::post('/shop/ai-chat/confirm', [ShopAiChatController::class, 'confirm'])->name('front.shop.ai-chat.confirm');
// Chat route for plant maintenance assistant
Route::get('/chat', function () {
    return view('chat');
})->name('front.chat');

// Cart & Orders for authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/cart', [\App\Http\Controllers\Front\CartController::class, 'index'])->name('front.cart');
    Route::post('/cart/add/{product}', [\App\Http\Controllers\Front\CartController::class, 'add'])->name('front.cart.add');
    Route::post('/cart/item/{item}', [\App\Http\Controllers\Front\CartController::class, 'updateItem'])->name('front.cart.item.update');
    Route::delete('/cart/item/{item}', [\App\Http\Controllers\Front\CartController::class, 'removeItem'])->name('front.cart.item.remove');

    Route::get('/checkout', [\App\Http\Controllers\Front\CartController::class, 'checkout'])->name('front.checkout');
    Route::post('/checkout/place', [\App\Http\Controllers\Front\CartController::class, 'placeOrder'])->name('front.checkout.place');

    Route::get('/my-orders', [\App\Http\Controllers\Front\OrderController::class, 'index'])->name('front.orders.index');
    Route::post('/my-orders/{order}/delivered', [\App\Http\Controllers\Front\OrderController::class, 'confirmDelivery'])->name('front.orders.delivered');

    // (AI Order payment page removed; checkout flow used instead)
});

// Maintenance Client Routes
Route::controller(\App\Http\Controllers\Front\MaintenanceClientController::class)->group(function () {
    Route::get('/maintenance', 'index')->name('front.maintenance');
    Route::get('/maintenance/category/{categoryId}', 'show')->name('front.maintenance.category');
    Route::get('/maintenance/product/{productId}', 'showMaintenance')->name('front.maintenance.show');
});

// Favorites Routes
Route::controller(\App\Http\Controllers\Front\FavoriteController::class)->middleware('auth')->group(function () {
    Route::post('/favorites/toggle/{product}', 'toggle')->name('front.favorites.toggle');
    Route::get('/my-favorites', 'index')->name('front.favorites.index');
    Route::get('/my-notifications', 'notifications')->name('front.notifications.index');
});

// Gracefully handle legacy static HTML URLs (e.g., /event.html)
Route::get('/{slug}.html', function (string $slug) {
    $map = [
        'index' => route('front.home', [], false),
        'event' => route('front.event', [], false),
        'shop' => route('front.shop', [], false),
        'shop-details' => route('front.shop', [], false),
        'cart' => route('front.cart', [], false),
        'checkout' => route('front.checkout', [], false),
        'portfolio' => route('front.portfolio', [], false),
        'single-portfolio' => route('front.portfolio.single', [], false),
        'blog' => route('front.blog', [], false),
        'post' => route('front.blog.single', [], false),
        'maintenance' => route('front.maintenance', [], false),
    ];
    if (isset($map[$slug])) {
        return redirect($map[$slug], 301);
    }
    abort(404);
});

// Backward-compat: redirect legacy /home to /dashboard
Route::get('/home', function () {
    return redirect('/');
});
// Public statutes resource (provides route names like statutes.store used in views)
Route::resource('statutes', StatuteController::class)->except(['store', 'update']);
// Protected routes with profanity check
Route::post('statutes', [StatuteController::class, 'store'])->middleware('profanity.check')->name('statutes.store');
Route::put('statutes/{statute}', [StatuteController::class, 'update'])->middleware('profanity.check')->name('statutes.update');
Route::patch('statutes/{statute}', [StatuteController::class, 'update'])->middleware('profanity.check');

// Public comment routes for front-end blog
Route::post('statutes/{statute}/comentes', [ComenteController::class, 'store'])->middleware(['auth', 'profanity.check'])->name('comentes.store');
Route::put('comentes/{comente}', [ComenteController::class, 'update'])->middleware(['auth', 'profanity.check'])->name('comentes.update');
Route::delete('comentes/{comente}', [ComenteController::class, 'destroy'])->middleware('auth')->name('comentes.destroy');

// Public statute reaction route for front-end blog
Route::post('statutes/{statute}/reaction', [StatuteReactionController::class, 'toggle'])->middleware('auth')->name('statutes.reaction');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('sign-up', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('sign-up', [RegisterController::class, 'store'])->middleware('guest');
Route::get('sign-in', [SessionsController::class, 'create'])->middleware('guest')->name('login');
Route::post('sign-in', [SessionsController::class, 'store'])->middleware('guest');
Route::get('auth/google/redirect', [GoogleController::class, 'redirect'])->middleware('guest')->name('oauth.google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('oauth.google.callback');
Route::post('verify', [SessionsController::class, 'show'])->middleware('guest');
Route::post('reset-password', [SessionsController::class, 'update'])->middleware('guest')->name('password.update');
Route::get('verify', function () {
    return view('dashboard.auth.sessions.password.verify');
})->middleware('guest')->name('verify');
Route::get('/reset-password/{token}', function ($token) {
    return view('dashboard.auth.sessions.password.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('sign-out', [SessionsController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('profile', [ProfileController::class, 'create'])->middleware('auth')->name('profile');
Route::patch('profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
// Legacy route kept for compatibility with old view; use named 'profile.update' for updates
Route::post('user-profile', [ProfileController::class, 'update'])->middleware('auth');
// Change password for authenticated user
Route::post('profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth')->name('profile.password');

Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('user-profile', function () {
        return view('dashboard.pages.laravel-examples.user-profile');
    })->name('user-profile');
});

// Admin-only routes
Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('admin/shop', function () {
        return redirect()->route('admin.shop.categories');
    })->name('admin.shop');
    Route::get('admin/shop/categories', [AdminShopDashboardController::class, 'categories'])->name('admin.shop.categories');
    Route::get('admin/shop/products', [AdminShopDashboardController::class, 'products'])->name('admin.shop.products');
    Route::get('admin/shop/orders', [AdminShopDashboardController::class, 'orders'])->name('admin.shop.orders');

    // (AI Report removed)

    // Notifications routes
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::put('/{notification}', [NotificationController::class, 'update'])->name('update');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    // New: JSON endpoint for admin event calendar data
    Route::get('admin/event/calendar-data', [\App\Http\Controllers\Admin\Event\EventController::class, 'calendarData'])->name('admin.event.calendar-data');

Route::prefix('admin/event')->name('admin.event.')->group(function () {
    Route::get('export-pdf', [\App\Http\Controllers\Admin\Event\EventController::class, 'exportPdf'])->name('export-pdf');
});


    // Resource routes for admin events (place after specific routes so specific URIs like "export" aren't captured by the {event} wildcard)
    Route::resource('admin/event', \App\Http\Controllers\Admin\Event\EventController::class)->names('admin.event');

    Route::get('admin/maintenance/categories', [MaintenanceController::class, 'index'])->name('admin.maintenance.categories');
    Route::get('admin/maintenance/categories/{categoryId}', [MaintenanceController::class, 'show'])->name('maintenance.category');
    Route::get('admin/maintenance', function () {
        return redirect()->route('admin.maintenance.categories');
    })->name('admin.maintenance');
    Route::delete('admin/maintenance/{productId}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
    Route::get('admin/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('admin/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('admin/maintenance/{productId}', [MaintenanceController::class, 'showMaintenance'])->name('maintenance.show');
    Route::get('admin/maintenance/{productId}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
    Route::match(['put', 'patch'], 'admin/maintenance/{productId}', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::get('admin/maintenance/{productId}/notifications', [NotificationController::class, 'showProductNotifications'])->name('maintenance.product.notifications');
    Route::get('admin/maintenance/{productId}/pdf', [MaintenanceController::class, 'downloadPdf'])->name('maintenance.pdf');
Route::get('/maintenance/generate-steps/{productName}', [MaintenanceController::class, 'generateSteps'])
    ->name('maintenance.generateSteps');
    Route::get('admin/blog', [StatuteController::class, 'dashboardIndex'])->name('admin.blog');

    Route::prefix('admin/shop-management')->name('admin.shop.')->group(function () {
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::post('products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
        Route::post('products/{product}/inventory', [AdminProductController::class, 'updateInventory'])->name('products.inventory');
        Route::post('products/{product}/promotions/{promotion}', [AdminProductController::class, 'attachPromotion'])->name('products.promotions.attach');
        Route::delete('products/{product}/promotions/{promotion}', [AdminProductController::class, 'detachPromotion'])->name('products.promotions.detach');
        Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])->name('products.restore');
        Route::post('products/{product}/notifications', [NotificationController::class, 'attachToProduct'])->name('products.notifications.attach');
    Route::put('products/{product}/notifications/{notification}', [NotificationController::class, 'updateProductNotification'])->name('products.notifications.update');
    Route::delete('products/{product}/notifications/{notification}', [NotificationController::class, 'detachFromProduct'])->name('products.notifications.detach');

        Route::resource('categories', AdminCategoryController::class)->except(['create', 'edit', 'show']);
        Route::post('categories/reorder', [AdminCategoryController::class, 'reorder'])->name('categories.reorder');
        Route::put('categories/{category}/sub-categories/{subCategory}', [AdminCategoryController::class, 'updateSubCategory'])->name('categories.sub.update');
        Route::delete('categories/{category}/sub-categories/{subCategory}', [AdminCategoryController::class, 'destroySubCategory'])->name('categories.sub.destroy');

        // Orders actions
        Route::post('orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
        Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    });

    Route::group(['prefix' => 'user-management', 'as' => 'user-management.'], function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/block', [UserManagementController::class, 'block'])->name('block');
        Route::post('/{user}/unblock', [UserManagementController::class, 'unblock'])->name('unblock');
    });

    // Admin blog/statutes routes (corrected prefixes to avoid conflicts)
    Route::get('admin/blog/index', [StatuteController::class, 'index'])->name('admin.blog.index');
Route::post('admin/statutes', [StatuteController::class, 'store'])->middleware('profanity.check')->name('admin.statutes.store');
    Route::put('admin/statutes/{statute}', [StatuteController::class, 'update'])->middleware('profanity.check')->name('admin.statutes.update');
    Route::patch('admin/statutes/{statute}', [StatuteController::class, 'update'])->middleware('profanity.check');
    Route::delete('admin/statutes/{statute}', [StatuteController::class, 'destroy'])->name('admin.statutes.destroy');
    // Nested comments routes (corrected prefixes)
    Route::post('admin/statutes/{statute}/comentes', [ComenteController::class, 'store'])->middleware('profanity.check')->name('admin.comentes.store');
    Route::put('admin/comentes/{comente}', [ComenteController::class, 'update'])->middleware('profanity.check')->name('admin.comentes.update');
   
    Route::delete('admin/comentes/{comente}', [ComenteController::class, 'destroy'])->name('admin.comentes.destroy');
        });
    Route::prefix('admin/ai-report')->name('admin.ai-report.')->middleware('can:access-admin')->group(function () {
        Route::get('/', [AiReportController::class, 'index'])->name('index');
        Route::post('/generate', [AiReportController::class, 'generate'])->name('generate');
        Route::get('/latest', [AiReportController::class, 'latest'])->name('latest');
        Route::get('/list', [AiReportController::class, 'list'])->name('list');
        Route::get('/{report}/pdf', [AiReportController::class, 'downloadPdf'])->name('pdf');
        Route::delete('/{report}', [AiReportController::class, 'destroy'])->name('destroy');
    });