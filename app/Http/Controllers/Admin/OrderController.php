<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, CatalogService $catalog): View
    {
        $query = Order::query()->with('items');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhere('sender_phone', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($region = $request->query('region')) {
            $query->where('region', $region);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = [
            'all' => Order::query()->count(),
            'paid' => Order::query()->where('status', 'paid')->count(),
            'packing' => Order::query()->where('status', 'packing')->count(),
            'shipping' => Order::query()->where('status', 'shipping')->count(),
            'delivered' => Order::query()->where('status', 'delivered')->count(),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'regions' => $catalog->regions(),
            'statusDefinitions' => config('catalog.order_statuses'),
            'statusColors' => config('catalog.status_colors'),
            'products' => Product::query()->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function show(Order $order, CatalogService $catalog): View
    {
        $order->load('items.product');

        return view('admin.orders.show', [
            'order' => $order,
            'regions' => $catalog->regions(),
            'districtsByRegion' => $catalog->districtsByRegion(),
            'statusDefinitions' => config('catalog.order_statuses'),
            'statusColors' => config('catalog.status_colors'),
            'products' => Product::query()->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request, CatalogService $catalog): RedirectResponse
    {
        $data = $request->validate([
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_phone' => ['required', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:40'],
            'region' => ['required', 'string', 'max:80'],
            'district' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:200'],
            'landmark' => ['nullable', 'string', 'max:200'],
            'wish' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:card,sbp'],
            'status' => ['required', 'in:paid,packing,shipping,delivered'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $settings = $catalog->settings();
        $qty = (int) $data['quantity'];
        $itemsTotal = $product->price_rub * $qty;
        $giftWrapFee = 0;
        $totalRub = $itemsTotal;

        do {
            $orderNumber = (string) random_int(483000, 999999);
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        $rate = $data['payment_method'] === 'sbp'
            ? (float) ($settings['rate_sbp_uzs'] ?? 156.25)
            : (float) ($settings['rate_card_uzs'] ?? 158.00);

        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'status' => $data['status'],
            'sender_name' => $data['sender_name'],
            'sender_phone' => $data['sender_phone'],
            'recipient_name' => $data['recipient_name'],
            'recipient_phone' => $data['recipient_phone'],
            'region' => $data['region'],
            'district' => $data['district'],
            'address' => $data['address'],
            'landmark' => $data['landmark'] ?? null,
            'wish' => $data['wish'] ?? null,
            'payment_method' => $data['payment_method'],
            'exchange_rate' => $rate,
            'card_masked' => $data['payment_method'] === 'sbp' ? 'SBP •0000' : 'MIR •4417',
            'items_total_rub' => $itemsTotal,
            'discount_rub' => 0,
            'oversize_fee_rub' => 0,
            'gift_wrap_fee_rub' => $giftWrapFee,
            'delivery_fee_rub' => 0,
            'total_rub' => $totalRub,
            'estimated_delivery_text' => '25 noyabr',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'title' => $product->title,
            'quantity' => $qty,
            'qty_label' => $qty.' dona',
            'unit_price_rub' => $product->price_rub,
            'total_price_rub' => $itemsTotal,
            'image' => $product->image ?: $product->gallery,
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('admin.flash.order_created', ['number' => $order->order_number]));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,packing,shipping,delivered'],
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_phone' => ['required', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:40'],
            'recipient_phone_2' => ['nullable', 'string', 'max:40'],
            'region' => ['required', 'string', 'max:80'],
            'district' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:200'],
            'landmark' => ['nullable', 'string', 'max:200'],
            'wish' => ['nullable', 'string', 'max:500'],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'courier_phone' => ['nullable', 'string', 'max:40'],
            'estimated_delivery_text' => ['nullable', 'string', 'max:80'],
            'status_note_title' => ['nullable', 'string', 'max:160'],
            'status_note_text' => ['nullable', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'discount_rub' => ['nullable', 'integer', 'min:0'],
            'oversize_fee_rub' => ['nullable', 'integer', 'min:0'],
            'gift_wrap_fee_rub' => ['nullable', 'integer', 'min:0'],
            'delivery_fee_rub' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['status'] === 'delivered' && ! $order->delivered_at) {
            $data['delivered_at'] = now();
        }
        if ($data['status'] === 'cancelled' && ! $order->cancelled_at) {
            $data['cancelled_at'] = now();
        }

        $itemsTotal = (int) $order->items()->sum('total_price_rub') ?: $order->items_total_rub;
        $discount = (int) ($data['discount_rub'] ?? $order->discount_rub);
        $oversize = (int) ($data['oversize_fee_rub'] ?? $order->oversize_fee_rub);
        $giftWrap = (int) ($data['gift_wrap_fee_rub'] ?? $order->gift_wrap_fee_rub);
        $delivery = (int) ($data['delivery_fee_rub'] ?? $order->delivery_fee_rub);

        $data['items_total_rub'] = $order->items_total_rub ?: $itemsTotal;
        $data['total_rub'] = max(0, $data['items_total_rub'] - $discount + $oversize + $giftWrap + $delivery);

        $order->update($data);

        return back()->with('success', __('admin.flash.order_updated', ['number' => $order->order_number]));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,packing,shipping,delivered'],
            'courier_name' => ['nullable', 'string', 'max:100'],
        ]);

        $payload = ['status' => $data['status']];
        if (! empty($data['courier_name'])) {
            $payload['courier_name'] = $data['courier_name'];
        }
        if ($data['status'] === 'delivered' && ! $order->delivered_at) {
            $payload['delivered_at'] = now();
        }
        if ($data['status'] === 'cancelled' && ! $order->cancelled_at) {
            $payload['cancelled_at'] = now();
        }

        $order->update($payload);

        $badge = config("catalog.order_statuses.{$data['status']}.badge", $data['status']);

        return back()->with('success', __('admin.flash.order_status_updated', ['number' => $order->order_number, 'badge' => $badge]));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $num = $order->order_number;
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', __('admin.flash.order_deleted', ['number' => $num]));
    }
}
