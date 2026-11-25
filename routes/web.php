<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | Yubor Market — Storefront (Mobile Marketplace Frontend)
 |--------------------------------------------------------------------------
 */

Route::middleware('storefront.script')->group(function () {
    Route::get('/script/{script}', function (Request $request, string $script) {
        if (! in_array($script, ['lat', 'cyrl'], true)) {
            $script = 'lat';
        }
        $request->session()->put('storefront_script', $script);

        return back()->withCookie(cookie('storefront_script', $script, 60 * 24 * 365));
    })->name('storefront.script');

    Route::get('/', [StorefrontController::class, 'home'])->name('home');
    Route::get('/category/{slug}', [StorefrontController::class, 'category'])->name('category');
    Route::get('/product/{slug}', [StorefrontController::class, 'product'])->name('product');
    Route::get('/product/{slug}/specs', [StorefrontController::class, 'specs'])->name('product.specs');
    Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/payment', [StorefrontController::class, 'payment'])->name('checkout.payment');
    Route::match(['get', 'post'], '/checkout/processing', [StorefrontController::class, 'processing'])->name('checkout.processing');
    Route::get('/orders/{id}/status', [StorefrontController::class, 'orderStatus'])->name('order.status');
    Route::get('/orders/{id}', [StorefrontController::class, 'order'])->name('order');
});

/*
 |--------------------------------------------------------------------------
 | Yubor Market — Admin Panel (/admin)
 |--------------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware('admin.locale')->group(function () {
    Route::get('/locale/{locale}', function (Request $request, string $locale) {
        if (! in_array($locale, ['uz', 'ru'], true)) {
            $locale = 'uz';
        }
        $request->session()->put('admin_locale', $locale);
        App::setLocale($locale);

        return back()
            ->withCookie(cookie('admin_locale', $locale, 60 * 24 * 365))
            ->with('success', __('admin.flash.locale_switched'));
    })->name('locale');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Banners
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

    // Regions & Districts
    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::put('/regions/{region}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');
    Route::post('/regions/{region}/districts', [RegionController::class, 'storeDistrict'])->name('regions.districts.store');
    Route::delete('/districts/{district}', [RegionController::class, 'destroyDistrict'])->name('districts.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/cards', [SettingController::class, 'updateCards'])->name('settings.cards');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
