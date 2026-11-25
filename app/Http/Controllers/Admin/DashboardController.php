<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Services\CatalogService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CatalogService $catalog): View
    {
        $orders = Order::query()->with('items')->latest()->get();

        $totalRevenueRub = (int) $orders->sum('total_rub');
        $totalRevenueUzs = (int) $orders->sum(fn (Order $o) => $o->total_uzs);
        $avgCheckRub = $orders->count() > 0
            ? (int) round($totalRevenueRub / $orders->count())
            : 0;

        $statusCounts = [
            'paid' => $orders->where('status', 'paid')->count(),
            'packing' => $orders->where('status', 'packing')->count(),
            'shipping' => $orders->where('status', 'shipping')->count(),
            'delivered' => $orders->where('status', 'delivered')->count(),
        ];

        $productsCount = Product::query()->count();
        $activeProductsCount = Product::query()->where('is_active', true)->count();
        $lowStockProducts = Product::query()
            ->with('category')
            ->where('stock', '<=', 15)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $categories = Category::query()->withCount('products')->orderBy('sort_order')->get();
        $bannersCount = Banner::query()->where('is_active', true)->count();
        $regionsCount = Region::query()->where('is_active', true)->count();
        $settings = $catalog->settings();

        return view('admin.dashboard', [
            'totalRevenueRub' => $totalRevenueRub,
            'totalRevenueUzs' => $totalRevenueUzs,
            'avgCheckRub' => $avgCheckRub,
            'ordersCount' => $orders->count(),
            'activeOrdersCount' => $statusCounts['paid'] + $statusCounts['packing'] + $statusCounts['shipping'],
            'statusCounts' => $statusCounts,
            'recentOrders' => $orders->take(8),
            'productsCount' => $productsCount,
            'activeProductsCount' => $activeProductsCount,
            'lowStockProducts' => $lowStockProducts,
            'categories' => $categories,
            'bannersCount' => $bannersCount,
            'regionsCount' => $regionsCount,
            'settings' => $settings,
            'statusDefinitions' => config('catalog.order_statuses'),
            'statusColors' => config('catalog.status_colors'),
        ]);
    }
}
