<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(CatalogService $catalog): View
    {
        $latestActiveOrder = Order::query()
            ->with('items')
            ->whereIn('status', ['paid', 'packing', 'shipping'])
            ->latest('id')
            ->first();

        $serverOrders = Order::query()
            ->with('items')
            ->latest('id')
            ->get()
            ->mapWithKeys(fn (Order $o) => [
                $o->order_number => [
                    'id' => $o->order_number,
                    'status' => $o->status,
                    'createdAt' => $o->created_at?->toIso8601String(),
                    'date' => $o->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
                    'recipient' => $o->recipient_name,
                    'region' => $o->region,
                    'district' => $o->district,
                    'total' => $o->formatted_total,
                    'items_count' => (int) ($o->items->count() ?: 1),
                    'images' => $o->items->pluck('image')->filter()->take(4)->map(fn ($img) => asset($img))->values()->all(),
                ],
            ])
            ->all();

        return view('pages.home', [
            'categories' => $catalog->categories(),
            'products' => $catalog->products(),
            'banners' => $catalog->banners(),
            'order' => $latestActiveOrder ? $latestActiveOrder->toCatalogOrderArray() : $catalog->defaultOrderArray(),
            'latestActiveOrderModel' => $latestActiveOrder,
            'serverOrders' => $serverOrders,
        ]);
    }

    public function category(string $slug, CatalogService $catalog): View
    {
        $category = $catalog->findCategory($slug) ?? abort(404);
        $products = $catalog->products($slug);

        return view('pages.category', compact('category', 'products'));
    }

    public function product(string $slug, CatalogService $catalog): View
    {
        $product = $catalog->findProduct($slug) ?? abort(404);
        $settings = $catalog->settings();

        return view('pages.product', compact('product', 'settings'));
    }

    public function specs(string $slug, CatalogService $catalog): View
    {
        $product = $catalog->findProduct($slug) ?? abort(404);

        $groups = $product['spec_groups'] ?? [
            'Asosiy' => ['Brend' => $product['brand'], 'Model' => $product['title']],
            'Xususiyatlar' => $product['specs'] ?? config('catalog.specs'),
        ];

        return view('pages.specs', compact('product', 'groups'));
    }

    public function checkout(Request $request, CatalogService $catalog): View
    {
        $selectedProduct = null;
        if ($slug = $request->query('product')) {
            $selectedProduct = $catalog->findProduct((string) $slug);
        }

        return view('pages.checkout', [
            'regions' => $catalog->regions(),
            'districts' => $catalog->districtsByRegion(),
            'settings' => $catalog->settings(),
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function payment(Request $request, CatalogService $catalog): View
    {
        $selectedProduct = null;
        if ($slug = $request->query('product')) {
            $selectedProduct = $catalog->findProduct((string) $slug);
        }

        return view('pages.payment', [
            'settings' => $catalog->settings(),
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function processing(Request $request, CatalogService $catalog): View
    {
        $orderNumber = config('catalog.order.id', '482913');
        $totalRub = 78160;
        $cardMasked = $request->input('method') === 'sbp' ? 'SBP •8821' : 'MIR •4417';

        // If checkout details were submitted from payment page, persist to DB
        if ($request->filled('recipient_name') && $request->filled('region')) {
            try {
                $settings = $catalog->settings();
                $method = $request->input('method', 'card') === 'sbp' ? 'sbp' : 'card';
                $rate = $method === 'sbp'
                    ? (float) ($settings['rate_sbp_uzs'] ?? 156.250)
                    : (float) ($settings['rate_card_uzs'] ?? 158.00);

                $productModel = null;
                $slug = $request->input('product_slug') ?: $request->input('product');
                if ($slug) {
                    $productModel = Product::query()->with('category')->where('slug', (string) $slug)->first();
                }

                do {
                    $orderNumber = (string) random_int(483000, 999999);
                } while (Order::query()->where('order_number', $orderNumber)->exists());

                $giftWrap = (int) ($settings['gift_wrap_fee_rub'] ?? 190);

                if ($productModel) {
                    $itemsTotal = (int) $productModel->price_rub;
                    $oversize = $productModel->category?->slug === 'maishiy-texnika'
                        ? (int) ($settings['oversize_fee_rub'] ?? 990)
                        : 0;
                    $totalRub = $itemsTotal + $giftWrap + $oversize;

                    $newOrder = Order::query()->create([
                        'order_number' => $orderNumber,
                        'status' => 'paid',
                        'sender_name' => (string) ($request->input('sender_name') ?: 'Haridor (Rossiya)'),
                        'sender_phone' => (string) ($request->input('sender_phone') ?: '+7 900 000 - 00 - 00'),
                        'recipient_name' => (string) $request->input('recipient_name'),
                        'recipient_phone' => (string) ($request->input('recipient_phone') ?: '+998 90 123-45-67'),
                        'recipient_phone_2' => $request->input('recipient_phone_2'),
                        'region' => (string) $request->input('region'),
                        'district' => (string) $request->input('district', ''),
                        'address' => (string) $request->input('address', ''),
                        'landmark' => $request->input('landmark'),
                        'wish' => $request->input('wish'),
                        'payment_method' => $method,
                        'exchange_rate' => $rate,
                        'card_masked' => $cardMasked,
                        'items_total_rub' => $itemsTotal,
                        'discount_rub' => 0,
                        'oversize_fee_rub' => $oversize,
                        'gift_wrap_fee_rub' => $giftWrap,
                        'delivery_fee_rub' => 0,
                        'total_rub' => $totalRub,
                        'estimated_delivery_text' => '25 noyabr',
                    ]);

                    $newOrder->items()->create([
                        'product_id' => $productModel->id,
                        'title' => $productModel->title,
                        'quantity' => 1,
                        'qty_label' => '1 dona',
                        'unit_price_rub' => $productModel->price_rub,
                        'total_price_rub' => $productModel->price_rub,
                        'image' => $productModel->image ?: $productModel->gallery,
                    ]);
                } else {
                    $itemsTotal = 76980;
                    $oversize = (int) ($settings['oversize_fee_rub'] ?? 990);
                    $totalRub = $itemsTotal + $giftWrap + $oversize;

                    $newOrder = Order::query()->create([
                        'order_number' => $orderNumber,
                        'status' => 'paid',
                        'sender_name' => (string) ($request->input('sender_name') ?: 'Haridor (Rossiya)'),
                        'sender_phone' => (string) ($request->input('sender_phone') ?: '+7 900 000 - 00 - 00'),
                        'recipient_name' => (string) $request->input('recipient_name'),
                        'recipient_phone' => (string) ($request->input('recipient_phone') ?: '+998 90 123-45-67'),
                        'recipient_phone_2' => $request->input('recipient_phone_2'),
                        'region' => (string) $request->input('region'),
                        'district' => (string) $request->input('district', ''),
                        'address' => (string) $request->input('address', ''),
                        'landmark' => $request->input('landmark'),
                        'wish' => $request->input('wish'),
                        'payment_method' => $method,
                        'exchange_rate' => $rate,
                        'card_masked' => $cardMasked,
                        'items_total_rub' => $itemsTotal,
                        'discount_rub' => 0,
                        'oversize_fee_rub' => $oversize,
                        'gift_wrap_fee_rub' => $giftWrap,
                        'delivery_fee_rub' => 0,
                        'total_rub' => $totalRub,
                        'estimated_delivery_text' => '25 noyabr',
                    ]);

                    $productMap = Product::query()
                        ->whereIn('slug', ['galaxy-a55', 'artel-muzlatgich-345', 'ayiqcha-80'])
                        ->get()
                        ->keyBy('slug');

                    $newOrder->items()->createMany([
                        [
                            'product_id' => $productMap->get('galaxy-a55')?->id,
                            'title' => 'Galaxy A55 5G, 8/256 GB',
                            'quantity' => 1,
                            'qty_label' => '1 dona',
                            'unit_price_rub' => 32990,
                            'total_price_rub' => 32990,
                            'image' => 'img/gallery-a55.png',
                        ],
                        [
                            'product_id' => $productMap->get('artel-muzlatgich-345')?->id,
                            'title' => 'Muzlatgich Artel 345 L',
                            'quantity' => 1,
                            'qty_label' => '1 dona',
                            'unit_price_rub' => 38990,
                            'total_price_rub' => 38990,
                            'image' => 'img/thumb-2.png',
                        ],
                        [
                            'product_id' => $productMap->get('ayiqcha-80')?->id,
                            'title' => 'Yumshoq ayiqcha, 80 sm',
                            'quantity' => 2,
                            'qty_label' => '2 dona',
                            'unit_price_rub' => 2490,
                            'total_price_rub' => 4980,
                            'image' => 'img/thumb-3.png',
                        ],
                    ]);
                }
            } catch (\Throwable) {
            }
        }

        return view('pages.processing', [
            'orderNumber' => $orderNumber,
            'totalRub' => $totalRub,
            'cardMasked' => $cardMasked,
        ]);
    }

    public function order(Request $request, string $id, CatalogService $catalog): View
    {
        $statuses = config('catalog.order_statuses');
        $success = $request->boolean('success');
        $orderModel = $catalog->findOrderModel($id);

        if ($success) {
            $key = 'paid';
            if ($orderModel && ! $request->filled('status') && $orderModel->status !== 'paid') {
                $orderModel->update(['status' => 'paid']);
            }
        } elseif ($request->filled('status')) {
            $key = (string) $request->query('status');
        } elseif ($orderModel) {
            $key = $orderModel->status;
        } else {
            $key = 'shipping';
        }

        abort_unless(isset($statuses[$key]), 404);

        $statusDef = $catalog->buildOrderStatusDefinition($key, $orderModel);

        return view('pages.order', [
            'success' => $success,
            'statusKey' => $key,
            'status' => $statusDef,
            'colors' => config('catalog.status_colors')[$statusDef['color']],
            'orderModel' => $orderModel,
            'order' => $orderModel ? $orderModel->toCatalogOrderArray() : config('catalog.order'),
            'settings' => $catalog->settings(),
        ]);
    }
}
