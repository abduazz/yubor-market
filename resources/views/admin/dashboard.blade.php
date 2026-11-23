<x-admin.layouts.app :heading="__('admin.dashboard.heading')" :subheading="__('admin.dashboard.subheading')">
    <x-slot:actions>
        <div class="hidden items-center gap-[8px] rounded-[12px] bg-[#f1f5f8] px-[12px] py-[8px] text-[12px] sm:flex">
            <span class="text-[#737373]">{{ __('admin.dashboard.rate_card') }}</span>
            <strong class="text-ink">1 ₽ = {{ $settings['rate_card_uzs'] }} UZS</strong>
            <span class="text-[#dbe4ee]">|</span>
            <span class="text-[#737373]">{{ __('admin.dashboard.rate_sbp') }}</span>
            <strong class="text-[#227bfc]">1 ₽ = {{ $settings['rate_sbp_uzs'] }} UZS</strong>
        </div>
        <a href="{{ route('admin.products.create') }}"
           class="flex items-center gap-[6px] rounded-[12px] bg-[#227bfc] px-[14px] py-[10px] text-[13px] font-semibold text-white transition hover:bg-[#1a68db]">
            <span>{{ __('admin.dashboard.add_product') }}</span>
        </a>
    </x-slot:actions>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-[16px] sm:grid-cols-2 xl:grid-cols-4">
        <div class="flex flex-col justify-between gap-[10px] rounded-[20px] bg-white p-[20px] shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[13px] font-medium text-[#737373]">{{ __('admin.dashboard.total_revenue') }}</span>
                <span class="rounded-[10px] bg-[#e8f1fe] px-[8px] py-[4px] text-[11px] font-semibold text-[#227bfc]">RUB → UZS</span>
            </div>
            <div>
                <p class="text-[26px] leading-tight font-bold text-ink">{{ number_format($totalRevenueRub, 0, '.', ' ') }} ₽</p>
                <p class="mt-[4px] text-[12px] text-[#737373]">≈ {{ number_format($totalRevenueUzs, 0, '.', ' ') }} UZS</p>
            </div>
        </div>

        <div class="flex flex-col justify-between gap-[10px] rounded-[20px] bg-white p-[20px] shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[13px] font-medium text-[#737373]">{{ __('admin.dashboard.total_orders') }}</span>
                <span class="rounded-[10px] bg-[#d1fadf] px-[8px] py-[4px] text-[11px] font-semibold text-[#039855]">{{ __('admin.dashboard.in_progress', ['count' => $activeOrdersCount]) }}</span>
            </div>
            <div>
                <p class="text-[26px] leading-tight font-bold text-ink">{{ $ordersCount }}</p>
                <p class="mt-[4px] text-[12px] text-[#737373]">{{ __('admin.dashboard.avg_check') }} <strong class="text-ink">{{ number_format($avgCheckRub, 0, '.', ' ') }} ₽</strong></p>
            </div>
        </div>

        <div class="flex flex-col justify-between gap-[10px] rounded-[20px] bg-white p-[20px] shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[13px] font-medium text-[#737373]">{{ __('admin.dashboard.catalog_products') }}</span>
                <span class="rounded-[10px] bg-[#f1f5f8] px-[8px] py-[4px] text-[11px] font-semibold text-[#525252]">{{ __('admin.dashboard.categories_count', ['count' => $categories->count()]) }}</span>
            </div>
            <div>
                <p class="text-[26px] leading-tight font-bold text-ink">{{ $activeProductsCount }} <span class="text-[15px] font-normal text-[#737373]">/ {{ $productsCount }}</span></p>
                <p class="mt-[4px] text-[12px] text-[#737373]">{{ __('admin.dashboard.active_banners') }} <strong class="text-ink">{{ $bannersCount }}</strong></p>
            </div>
        </div>

        <div class="flex flex-col justify-between gap-[10px] rounded-[20px] bg-white p-[20px] shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[13px] font-medium text-[#737373]">{{ __('admin.dashboard.delivery_geography') }}</span>
                <span class="rounded-[10px] bg-[#fef0c7] px-[8px] py-[4px] text-[11px] font-semibold text-[#b54708]">{{ __('admin.uzbekistan') }}</span>
            </div>
            <div>
                <p class="text-[26px] leading-tight font-bold text-ink">{{ __('admin.dashboard.regions_count', ['count' => $regionsCount]) }}</p>
                <p class="mt-[4px] text-[12px] text-[#737373]">{{ $settings['delivery_provider_text'] }}</p>
            </div>
        </div>
    </div>

    {{-- Order Status Pipeline --}}
    <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
        <div class="mb-[14px] flex items-center justify-between">
            <h2 class="text-[16px] font-bold text-ink">{{ __('admin.dashboard.funnel_title') }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-[13px] font-medium text-[#227bfc] hover:underline">{{ __('admin.dashboard.all_orders_link') }}</a>
        </div>
        <div class="grid grid-cols-2 gap-[12px] sm:grid-cols-5">
            @foreach ($statusDefinitions as $key => $def)
                @php
                    $palette = $statusColors[$def['color']] ?? ['bg' => '#eef2f6', 'text' => '#475467', 'accent' => '#227bfc'];
                    $count = $statusCounts[$key] ?? 0;
                @endphp
                <a href="{{ route('admin.orders.index', ['status' => $key]) }}"
                   class="flex flex-col justify-between gap-[8px] rounded-[16px] border border-[#eef2f6] p-[14px] transition hover:border-[#227bfc]/40 hover:bg-[#f8fafc]">
                    <div class="flex items-center justify-between">
                        <span class="rounded-[8px] px-[8px] py-[3px] text-[11px] font-semibold"
                              style="background: {{ $palette['bg'] }}; color: {{ $palette['text'] }}">
                            {{ $def['badge'] }}
                        </span>
                        <span class="text-[11px] font-medium text-[#737373]">{{ $def['step'] > 0 ? $def['step'].'/4' : '✕' }}</span>
                    </div>
                    <div>
                        <p class="text-[22px] font-bold text-ink">{{ $count }}</p>
                        <p class="truncate text-[12px] text-[#737373]">{{ $def['title'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        {{-- Recent Orders --}}
        <div class="flex flex-col rounded-[20px] bg-white p-[20px] shadow-2xs xl:col-span-2">
            <div class="mb-[16px] flex items-center justify-between">
                <div>
                    <h2 class="text-[16px] font-bold text-ink">{{ __('admin.dashboard.recent_orders') }}</h2>
                    <p class="text-[12px] text-[#737373]">{{ __('admin.dashboard.recent_orders_hint') }}</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="rounded-[10px] bg-[#f1f5f8] px-[12px] py-[7px] text-[12px] font-semibold text-ink hover:bg-[#e2e8f0]">
                    {{ __('admin.dashboard.open_list') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-[#eef2f6] text-[11px] font-semibold tracking-wider text-[#737373] uppercase">
                            <th class="pb-[10px] pr-[12px]">{{ __('admin.dashboard.col_order') }}</th>
                            <th class="pb-[10px] pr-[12px]">{{ __('admin.dashboard.col_sender_recipient') }}</th>
                            <th class="pb-[10px] pr-[12px]">{{ __('admin.dashboard.col_region') }}</th>
                            <th class="pb-[10px] pr-[12px]">{{ __('admin.dashboard.col_amount') }}</th>
                            <th class="pb-[10px] pr-[12px]">{{ __('admin.dashboard.col_status') }}</th>
                            <th class="pb-[10px] text-right">{{ __('admin.dashboard.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef2f6]">
                        @foreach ($recentOrders as $order)
                            @php
                                $def = $statusDefinitions[$order->status] ?? $statusDefinitions['shipping'];
                                $palette = $statusColors[$def['color']] ?? ['bg' => '#eef2f6', 'text' => '#475467'];
                            @endphp
                            <tr class="hover:bg-[#f8fafc]">
                                <td class="py-[12px] pr-[12px] whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-[#227bfc] hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                    <p class="text-[11px] text-[#737373]">{{ $order->created_at?->format('d.m.Y H:i') }}</p>
                                </td>
                                <td class="py-[12px] pr-[12px]">
                                    <p class="font-medium text-ink">{{ $order->recipient_name }}</p>
                                    <p class="text-[11px] text-[#737373]">{{ __('admin.dashboard.from_sender', ['name' => $order->sender_name]) }} · {{ $order->recipient_phone }}</p>
                                </td>
                                <td class="py-[12px] pr-[12px] whitespace-nowrap">
                                    <p class="font-medium text-ink">{{ $order->region }}</p>
                                    <p class="text-[11px] text-[#737373]">{{ $order->district }}</p>
                                </td>
                                <td class="py-[12px] pr-[12px] whitespace-nowrap">
                                    <p class="font-semibold text-ink">{{ $order->formatted_total }}</p>
                                    <p class="text-[11px] text-[#737373]">{{ $order->items->count() }} {{ __('admin.items_short') }}</p>
                                </td>
                                <td class="py-[12px] pr-[12px] whitespace-nowrap">
                                    <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                                class="cursor-pointer rounded-[8px] border-0 px-[8px] py-[4px] text-[12px] font-semibold outline-none"
                                                style="background: {{ $palette['bg'] }}; color: {{ $palette['text'] }}">
                                            @foreach ($statusDefinitions as $sKey => $sDef)
                                                <option value="{{ $sKey }}" @selected($order->status === $sKey)>
                                                    {{ $sDef['badge'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="py-[12px] text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-[6px]">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="rounded-[8px] bg-[#f1f5f8] px-[10px] py-[5px] text-[12px] font-medium text-ink hover:bg-[#e2e8f0]">
                                            {{ __('admin.details') }}
                                        </a>
                                        <a href="{{ route('order', $order->order_number) }}" target="_blank" title="{{ __('admin.open_storefront') }}"
                                           class="rounded-[8px] bg-[#e8f1fe] px-[8px] py-[5px] text-[12px] font-medium text-[#227bfc] hover:bg-[#d0e7fd]">
                                            ↗
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right column: Categories & Stock --}}
        <div class="flex flex-col gap-[20px]">
            <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
                <div class="mb-[12px] flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-ink">{{ __('admin.dashboard.storefront_categories') }}</h2>
                    <a href="{{ route('admin.categories.index') }}" class="text-[12px] font-medium text-[#227bfc] hover:underline">{{ __('admin.dashboard.manage_link') }}</a>
                </div>
                <div class="flex flex-col gap-[10px]">
                    @foreach ($categories as $cat)
                        <div class="flex items-center justify-between rounded-[14px] bg-[#f8fafc] px-[12px] py-[10px]">
                            <div class="flex items-center gap-[10px]">
                                @if ($cat->image)
                                    <img src="{{ asset($cat->image) }}" alt="" class="size-[36px] rounded-[8px] bg-white object-cover p-[2px]">
                                @endif
                                <div>
                                    <p class="text-[13px] font-semibold text-ink">{{ $cat->title }}</p>
                                    <p class="text-[11px] text-[#737373]">{{ __('admin.dashboard.on_home_cards', ['count' => $cat->demo_count]) }}</p>
                                </div>
                            </div>
                            <span class="rounded-[8px] bg-white px-[8px] py-[4px] text-[12px] font-semibold text-ink shadow-2xs">
                                {{ $cat->products_count }} {{ __('admin.dashboard.products_short') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
                <div class="mb-[12px] flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-ink">{{ __('admin.dashboard.stock_control') }}</h2>
                    <a href="{{ route('admin.products.index') }}" class="text-[12px] font-medium text-[#227bfc] hover:underline">{{ __('admin.dashboard.all_products_link') }}</a>
                </div>
                <div class="flex flex-col gap-[10px]">
                    @forelse ($lowStockProducts as $prod)
                        <div class="flex items-center justify-between gap-[10px] rounded-[14px] bg-[#f8fafc] px-[12px] py-[10px]">
                            <div class="flex min-w-0 items-center gap-[10px]">
                                <img src="{{ asset($prod->image) }}" alt="" class="size-[36px] shrink-0 rounded-[8px] bg-white object-cover">
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-medium text-ink">{{ $prod->title }}</p>
                                    <p class="text-[11px] text-[#737373]">{{ $prod->formatted_price }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-[8px] px-[8px] py-[3px] text-[11px] font-semibold {{ $prod->stock <= 10 ? 'bg-[#fee4e2] text-[#d92d20]' : 'bg-[#fef0c7] text-[#b54708]' }}">
                                {{ $prod->stock }} {{ __('admin.pcs') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-[13px] text-[#737373]">{{ __('admin.dashboard.stock_sufficient') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
