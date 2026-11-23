<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\Setting;

class CatalogService
{
    public function categories(): array
    {
        try {
            $items = Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($items->isNotEmpty()) {
                return $items->map(fn (Category $c) => $c->toCatalogArray())->all();
            }
        } catch (\Throwable) {
        }

        return config('catalog.categories', []);
    }

    public function banners(): array
    {
        try {
            $items = Banner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($items->isNotEmpty()) {
                return $items->map(fn (Banner $b) => $b->toCatalogArray())->all();
            }
        } catch (\Throwable) {
        }

        return config('catalog.banners', []);
    }

    public function products(?string $categorySlug = null): array
    {
        try {
            $query = Product::query()
                ->with('category')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id');

            if ($categorySlug !== null) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            }

            $items = $query->get();

            if ($items->isNotEmpty() || Product::query()->exists()) {
                return $items->map(fn (Product $p) => $p->toCatalogArray())->all();
            }
        } catch (\Throwable) {
        }

        $fallback = collect(config('catalog.products', []));
        if ($categorySlug !== null) {
            $fallback = $fallback->where('category', $categorySlug)->values();
        }

        return $fallback->all();
    }

    public function findCategory(string $slug): ?array
    {
        try {
            $category = Category::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if ($category) {
                return $category->toCatalogArray();
            }
        } catch (\Throwable) {
        }

        return collect(config('catalog.categories', []))->firstWhere('slug', $slug);
    }

    public function findProduct(string $slug): ?array
    {
        try {
            $product = Product::query()
                ->with('category')
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if ($product) {
                return $product->toCatalogArray();
            }
        } catch (\Throwable) {
        }

        return collect(config('catalog.products', []))->firstWhere('slug', $slug);
    }

    public function regions(): array
    {
        try {
            $regions = Region::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('name')
                ->all();

            if (! empty($regions)) {
                return $regions;
            }
        } catch (\Throwable) {
        }

        return config('catalog.regions', []);
    }

    public function districtsByRegion(): array
    {
        try {
            $regions = Region::query()
                ->where('is_active', true)
                ->with(['districts' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
                ->orderBy('sort_order')
                ->get();

            if ($regions->isNotEmpty()) {
                $map = [];
                foreach ($regions as $region) {
                    $map[$region->name] = $region->districts->pluck('name')->all();
                }

                return $map;
            }
        } catch (\Throwable) {
        }

        return config('catalog.districts', []);
    }

    public function settings(): array
    {
        $defaults = [
            'rate_card_uzs' => '158.00',
            'rate_sbp_uzs' => '156.250',
            'gift_wrap_fee_rub' => '190',
            'oversize_fee_rub' => '990',
            'delivery_provider_text' => '2-4 kunda EMU po‘chta orqali',
            'delivery_badge_text' => 'Yetkazish bepul · 2–3 kun (EMU)',
            'support_chat_url' => '#',
            'marketplace_phone' => '+998 71 200-00-00',
        ];

        try {
            $dbSettings = Setting::query()->pluck('value', 'key')->all();

            return array_merge($defaults, $dbSettings);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public function findOrderModel(string $orderNumber): ?Order
    {
        try {
            return Order::query()
                ->with('items')
                ->where('order_number', $orderNumber)
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    public function defaultOrderArray(): array
    {
        $model = $this->findOrderModel(config('catalog.order.id', '482913'));
        if ($model) {
            return $model->toCatalogOrderArray();
        }

        return config('catalog.order');
    }

    public function buildOrderStatusDefinition(string $statusKey, ?Order $order = null): array
    {
        $statuses = config('catalog.order_statuses');
        $status = $statuses[$statusKey] ?? $statuses['shipping'];

        if (! $order) {
            return $status;
        }

        if ($order->estimated_delivery_text && in_array($statusKey, ['paid', 'packing', 'shipping'], true)) {
            $status['subtitle'] = 'Taxminiy yetkazish: '.$order->estimated_delivery_text;
        }

        if ($statusKey === 'shipping' && $order->courier_name) {
            $status['note']['title'] = 'Kuryer: '.$order->courier_name;
        }

        if ($statusKey === 'delivered') {
            $status['note']['title'] = $order->recipient_name.' qabul qildi';
        }

        if ($statusKey === 'cancelled') {
            $status['note']['title'] = $order->formatted_total.' qaytariladi';
        }

        if ($order->status_note_title) {
            $status['note']['title'] = $order->status_note_title;
        }

        if ($order->status_note_text) {
            $status['note']['text'] = $order->status_note_text;
        }

        return $status;
    }
}
