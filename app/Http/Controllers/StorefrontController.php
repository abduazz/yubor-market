<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(CatalogService $catalog): View
    {
        $latestActiveOrder = Order::query()
            ->with('items')
            ->whereIn('status', ['paid', 'packing', 'shipping', 'delivered'])
            ->latest('updated_at')
            ->first();

        $serverOrders = Order::query()
            ->with('items')
            ->get()
            ->mapWithKeys(fn (Order $o) => [
                $o->order_number => [
                    'id' => $o->order_number,
                    'status' => $o->status,
                    'recipient' => $o->recipient_name,
                    'region' => $o->region,
                    'district' => $o->district,
                    'createdAt' => $o->created_at?->toIso8601String(),
                    'date' => $o->created_at?->format('d.m.Y'),
                    'total' => $o->formatted_total,
                    'items_count' => $o->items->count(),
                    'images' => $o->items->pluck('image')->take(3)->map(fn ($img) => asset($img))->values()->all(),
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

    public function product(Request $request, string $slug, CatalogService $catalog): View
    {
        $product = $catalog->findProduct($slug) ?? abort(404);
        $settings = $catalog->settings();

        $previous = url()->previous();
        $prevPath = parse_url($previous, PHP_URL_PATH) ?: '';
        if ($prevPath === '/' || str_starts_with($prevPath, '/category/')) {
            $request->session()->put('product_back_url', $previous);
        }
        $backUrl = $request->session()->get('product_back_url', route('home'));

        return view('pages.product', compact('product', 'settings', 'backUrl'));
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

        $previous = url()->previous();
        $prevPath = parse_url($previous, PHP_URL_PATH) ?: '';
        if (
            $prevPath !== ''
            && ! str_starts_with($prevPath, '/checkout')
            && ! str_starts_with($prevPath, '/orders')
            && ! str_starts_with($prevPath, '/admin')
        ) {
            $request->session()->put('checkout_back_url', $previous);
        }

        $defaultBack = $selectedProduct
            ? route('product', $selectedProduct['slug'])
            : route('home');
        $backUrl = $selectedProduct
            ? route('product', $selectedProduct['slug'])
            : $request->session()->get('checkout_back_url', $defaultBack);

        $profileConfig = config('catalog.sender_profile', [
            'name' => 'Rustam Karimov',
            'phone' => '926 450 - 18 - 90',
        ]);

        $senderProfile = [
            'name' => (string) $request->query('sender_name', $profileConfig['name'] ?? 'Rustam Karimov'),
            'phone' => (string) $request->query('sender_phone', $profileConfig['phone'] ?? '926 450 - 18 - 90'),
        ];

        return view('pages.checkout', [
            'regions' => $catalog->regions(),
            'districts' => $catalog->districtsByRegion(),
            'settings' => $catalog->settings(),
            'selectedProduct' => $selectedProduct,
            'backUrl' => $backUrl,
            'senderProfile' => $senderProfile,
        ]);
    }

    public function payment(Request $request, CatalogService $catalog): View
    {
        $selectedProduct = null;
        if ($slug = $request->query('product') ?: $request->query('product_slug')) {
            $selectedProduct = $catalog->findProduct((string) $slug);
        }

        return view('pages.payment', [
            'settings' => $catalog->settings(),
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function method(Request $request, CatalogService $catalog): View
    {
        $selectedProduct = null;
        if ($slug = $request->query('product') ?: $request->query('product_slug')) {
            $selectedProduct = $catalog->findProduct((string) $slug);
        }

        return view('pages.method', [
            'settings' => $catalog->settings(),
            'selectedProduct' => $selectedProduct,
            'wish' => (string) $request->query('wish', ''),
        ]);
    }

    public function processing(Request $request, CatalogService $catalog): View
    {
        $orderNumber = config('catalog.order.id', '482913');
        $totalRub = 77970;
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
                if ($request->filled('product_slug')) {
                    $productModel = Product::query()->where('slug', (string) $request->input('product_slug'))->first();
                }

                if ($productModel) {
                    $itemsTotal = $productModel->price_rub;
                    $oversize = $productModel->category?->slug === 'maishiy-texnika' ? (int) ($settings['oversize_fee_rub'] ?? 990) : 0;
                    $totalRub = $itemsTotal + $oversize;

                    do {
                        $orderNumber = (string) random_int(483000, 999999);
                    } while (Order::query()->where('order_number', $orderNumber)->exists());

                    $newOrder = Order::query()->create([
                        'order_number' => $orderNumber,
                        'status' => 'paid',
                        'sender_name' => (string) $request->input('sender_name', 'Haridor (Rossiya)'),
                        'sender_phone' => (string) $request->input('sender_phone', '+7 900 000 - 00 - 00'),
                        'recipient_name' => (string) $request->input('recipient_name'),
                        'recipient_phone' => (string) $request->input('recipient_phone', '+998 90 123-45-67'),
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
                        'gift_wrap_fee_rub' => 0,
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
                    // Update demo order #482913 with submitted recipient & wish details
                    $demoOrder = Order::query()->where('order_number', $orderNumber)->first();
                    if ($demoOrder) {
                        $demoOrder->update([
                            'status' => 'paid',
                            'sender_name' => (string) $request->input('sender_name', $demoOrder->sender_name),
                            'sender_phone' => (string) $request->input('sender_phone', $demoOrder->sender_phone),
                            'recipient_name' => (string) $request->input('recipient_name', $demoOrder->recipient_name),
                            'recipient_phone' => (string) $request->input('recipient_phone', $demoOrder->recipient_phone),
                            'recipient_phone_2' => $request->input('recipient_phone_2', $demoOrder->recipient_phone_2),
                            'region' => (string) $request->input('region', $demoOrder->region),
                            'district' => (string) $request->input('district', $demoOrder->district),
                            'address' => (string) $request->input('address', $demoOrder->address),
                            'landmark' => $request->input('landmark', $demoOrder->landmark),
                            'wish' => $request->filled('wish') ? (string) $request->input('wish') : $demoOrder->wish,
                            'payment_method' => $method,
                            'exchange_rate' => $rate,
                            'card_masked' => $cardMasked,
                        ]);
                        $totalRub = $demoOrder->total_rub;
                    }
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

        if ($orderModel) {
            $key = isset($statuses[$orderModel->status]) ? $orderModel->status : 'paid';
        } elseif ($success) {
            $key = 'paid';
        } elseif ($request->filled('status') && isset($statuses[(string) $request->query('status')])) {
            $key = (string) $request->query('status');
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

    public function orderStatus(string $id, CatalogService $catalog): JsonResponse
    {
        $orderModel = $catalog->findOrderModel($id);
        if (! $orderModel) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'id' => $orderModel->order_number,
            'status' => $orderModel->status,
            'recipient' => $orderModel->recipient_name,
            'region' => $orderModel->region,
            'district' => $orderModel->district,
            'createdAt' => $orderModel->created_at?->toIso8601String(),
            'date' => $orderModel->created_at?->format('d.m.Y'),
            'total' => $orderModel->formatted_total,
            'items_count' => $orderModel->items->count(),
            'images' => $orderModel->items->pluck('image')->take(3)->map(fn ($img) => asset($img))->values()->all(),
        ]);
    }
}
