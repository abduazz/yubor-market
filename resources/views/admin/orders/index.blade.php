<x-admin.layouts.app :heading="__('admin.orders.heading')" :subheading="__('admin.orders.subheading')">
    <x-slot:actions>
        <button type="button" onclick="document.getElementById('create-order-modal').classList.remove('hidden')"
                class="rounded-[12px] bg-[#227bfc] px-[14px] py-[10px] text-[13px] font-semibold text-white transition hover:bg-[#1a68db]">
            {{ __('admin.orders.create_btn') }}
        </button>
    </x-slot:actions>

    {{-- Status Tabs --}}
    <div class="flex flex-wrap items-center gap-[8px]">
        <a href="{{ route('admin.orders.index', array_filter(['q' => request('q'), 'region' => request('region')])) }}"
           class="rounded-[12px] px-[12px] py-[8px] text-[13px] font-semibold transition {{ !request('status') ? 'bg-[#227bfc] text-white' : 'bg-white text-[#525252] hover:bg-[#e2e8f0]' }}">
            {{ __('admin.all') }} ({{ $statusCounts['all'] }})
        </a>
        @foreach ($statusDefinitions as $sKey => $sDef)
            @php $active = request('status') === $sKey; @endphp
            <a href="{{ route('admin.orders.index', array_filter(['status' => $sKey, 'q' => request('q'), 'region' => request('region')])) }}"
               class="rounded-[12px] px-[12px] py-[8px] text-[13px] font-semibold transition {{ $active ? 'bg-[#227bfc] text-white' : 'bg-white text-[#525252] hover:bg-[#e2e8f0]' }}">
                {{ $sDef['badge'] }} ({{ $statusCounts[$sKey] ?? 0 }})
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <form action="{{ route('admin.orders.index') }}" method="GET"
          class="flex flex-wrap items-center gap-[12px] rounded-[20px] bg-white p-[16px] shadow-2xs">
        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="min-w-[240px] flex-1">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('admin.orders.search_placeholder') }}"
                   class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] text-ink outline-none focus:border-[#227bfc] focus:bg-white">
        </div>
        <select name="region" onchange="this.form.submit()"
                class="h-[42px] rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] text-ink outline-none focus:border-[#227bfc]">
            <option value="">{{ __('admin.orders.all_regions') }}</option>
            @foreach ($regions as $r)
                <option value="{{ $r }}" @selected(request('region') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <button type="submit" class="h-[42px] rounded-[12px] bg-[#1c1917] px-[16px] text-[13px] font-semibold text-white hover:bg-[#333]">
            {{ __('admin.search_btn') }}
        </button>
        @if (request()->anyFilled(['q', 'status', 'region']))
            <a href="{{ route('admin.orders.index') }}" class="text-[13px] font-medium text-[#737373] hover:text-ink">
                {{ __('admin.reset') }}
            </a>
        @endif
    </form>

    {{-- Orders Table --}}
    <div class="overflow-hidden rounded-[20px] bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-[#eef2f6] bg-[#f8fafc] text-[11px] font-semibold tracking-wider text-[#737373] uppercase">
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_order_num') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_products') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_sender_ru') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_recipient_uz') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_address') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_payment') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.orders.col_status') }}</th>
                        <th class="px-[16px] py-[12px] text-right">{{ __('admin.orders.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eef2f6]">
                    @forelse ($orders as $order)
                        @php
                            $def = $statusDefinitions[$order->status] ?? $statusDefinitions['shipping'];
                            $palette = $statusColors[$def['color']] ?? ['bg' => '#eef2f6', 'text' => '#475467'];
                        @endphp
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-[14px] font-bold text-[#227bfc] hover:underline">
                                    #{{ $order->order_number }}
                                </a>
                                <p class="text-[11px] text-[#737373]">{{ $order->created_at?->format('d.m.Y H:i') }}</p>
                            </td>
                            <td class="px-[16px] py-[14px]">
                                <div class="flex items-center gap-[6px]">
                                    @foreach ($order->items->take(3) as $item)
                                        <img src="{{ asset($item->image) }}" alt="" title="{{ $item->title }} ({{ $item->qty_label }})"
                                             class="size-[36px] rounded-[8px] border border-[#eef2f6] bg-[#f8f7f5] object-cover">
                                    @endforeach
                                    <span class="text-[12px] font-medium text-[#525252]">{{ $order->items->count() }} {{ __('admin.items_short') }}</span>
                                </div>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <p class="font-medium text-ink">{{ $order->sender_name }}</p>
                                <p class="text-[11px] text-[#737373]">{{ $order->sender_phone }}</p>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <p class="font-medium text-ink">{{ $order->recipient_name }}</p>
                                <p class="text-[11px] text-[#737373]">{{ $order->recipient_phone }}</p>
                            </td>
                            <td class="px-[16px] py-[14px]">
                                <p class="font-medium text-ink">{{ $order->region }}, {{ $order->district }}</p>
                                <p class="max-w-[200px] truncate text-[11px] text-[#737373]">{{ $order->address }}</p>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <p class="font-bold text-ink">{{ $order->formatted_total }}</p>
                                <p class="text-[11px] text-[#737373]">{{ strtoupper($order->payment_method) }} · ≈ {{ $order->formatted_total_uzs }}</p>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="cursor-pointer rounded-[8px] border-0 px-[10px] py-[5px] text-[12px] font-semibold outline-none"
                                            style="background: {{ $palette['bg'] }}; color: {{ $palette['text'] }}">
                                        @foreach ($statusDefinitions as $sKey => $sDef)
                                            <option value="{{ $sKey }}" @selected($order->status === $sKey)>
                                                {{ $sDef['badge'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-[16px] py-[14px] text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-[6px]">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="rounded-[10px] bg-[#f1f5f8] px-[12px] py-[6px] text-[12px] font-semibold text-ink hover:bg-[#e2e8f0]">
                                        {{ __('admin.open') }}
                                    </a>
                                    <a href="{{ route('order', $order->order_number) }}" target="_blank" title="{{ __('admin.open_storefront') }}"
                                       class="rounded-[10px] bg-[#e8f1fe] px-[10px] py-[6px] text-[12px] font-semibold text-[#227bfc] hover:bg-[#d0e7fd]">
                                        {{ __('admin.storefront_short') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-[40px] text-center text-[14px] text-[#737373]">
                                {{ __('admin.orders.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t border-[#eef2f6] px-[16px] py-[12px]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- Create Order Modal --}}
    <div id="create-order-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-[16px]">
        <div class="max-h-[90dvh] w-full max-w-[600px] overflow-y-auto rounded-[24px] bg-white p-[24px] shadow-xl">
            <div class="mb-[16px] flex items-center justify-between">
                <h3 class="text-[18px] font-bold text-ink">{{ __('admin.orders.modal_title') }}</h3>
                <button type="button" onclick="document.getElementById('create-order-modal').classList.add('hidden')"
                        class="text-[20px] text-[#737373] hover:text-ink">✕</button>
            </div>
            <form action="{{ route('admin.orders.store') }}" method="POST" class="flex flex-col gap-[14px]">
                @csrf
                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-2">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.sender_name') }}</label>
                        <input type="text" name="sender_name" required placeholder="Alisher Karimov"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.sender_phone') }}</label>
                        <input type="text" name="sender_phone" required placeholder="+7 900 123 - 45 - 67"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.recipient_name') }}</label>
                        <input type="text" name="recipient_name" required placeholder="Mohira Karimova"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.recipient_phone') }}</label>
                        <input type="text" name="recipient_phone" required placeholder="+998 90 123-45-67"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.region_label') }}</label>
                        <select name="region" required class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            @foreach ($regions as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.district_label') }}</label>
                        <input type="text" name="district" required placeholder="Marg‘ilon shahri"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.address_label') }}</label>
                    <input type="text" name="address" required placeholder="Navro‘z mahallasi, Bog‘ ko‘chasi 12-uy"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>

                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.product_label') }}</label>
                        <select name="product_id" required class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} — {{ $p->formatted_price }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.qty_label') }}</label>
                        <input type="number" name="quantity" value="1" min="1" max="50" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-2">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.payment_method_label') }}</label>
                        <select name="payment_method" class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            <option value="card">{{ __('admin.orders.method_card') }}</option>
                            <option value="sbp">{{ __('admin.orders.method_sbp') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.initial_status_label') }}</label>
                        <select name="status" class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            @foreach ($statusDefinitions as $sKey => $sDef)
                                <option value="{{ $sKey }}">{{ $sDef['badge'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.wish_label') }}</label>
                    <textarea name="wish" rows="2" placeholder="{{ __('admin.orders.wish_placeholder') }}"
                              class="w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] p-[12px] text-[13px]"></textarea>
                </div>

                <div class="mt-[8px] flex justify-end gap-[10px]">
                    <button type="button" onclick="document.getElementById('create-order-modal').classList.add('hidden')"
                            class="rounded-[12px] bg-[#f1f5f8] px-[16px] py-[10px] text-[13px] font-medium text-ink">
                        {{ __('admin.cancel') }}
                    </button>
                    <button type="submit" class="rounded-[12px] bg-[#227bfc] px-[20px] py-[10px] text-[13px] font-semibold text-white">
                        {{ __('admin.orders.submit_create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
