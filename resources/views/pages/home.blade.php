@php
    $categories = $categories ?? config('catalog.categories');
    $products = $products ?? config('catalog.products');
    $banners = $banners ?? config('catalog.banners');
    $order = $order ?? config('catalog.order');
    // Har bir bo'lim uchun o'z tovarlari (demo_count tagacha takrorlanadi)
    $byCategory = function (array $category) use ($products) {
        $items = collect($products)->where('category', $category['slug'])->values();
        $count = $category['demo_count'] ?? $items->count();

        return $items->isEmpty() ? [] : collect(range(0, $count - 1))
            ->map(fn ($i) => $items[$i % $items->count()])->all();
    };
@endphp

<x-layouts.app title="Yubor Market">
    <div class="flex w-full flex-col gap-[12px] pb-[24px]">

        {{-- Yuqori oq blok: header + banner + kategoriyalar --}}
        <div class="flex w-full flex-col items-center gap-[16px] rounded-b-[24px] bg-white pb-[20px]">
            <nav class="flex w-full items-center justify-between px-[20px] py-[12px]">
                <a href="{{ url()->previous() }}" class="size-[28px] shrink-0" aria-label="Orqaga">
                    <img src="{{ asset('img/arrow-left.svg') }}" alt="" class="size-full">
                </a>
                <a href="{{ route('home') }}" class="flex items-center gap-[8px]">
                    <span class="flex items-center gap-[7.94px]">
                        <img src="{{ asset('img/logo-mark.svg') }}" alt="" class="h-[28px] w-[29.9px]">
                        <img src="{{ asset('img/logo-text.svg') }}" alt="YUBOR" class="h-[20.06px] w-[81.75px]">
                    </span>
                    <span class="flex items-center justify-center rounded-[49.65px] bg-[#2779f2] px-[5.96px] py-[2.98px] text-[12.91px] leading-normal font-medium text-white">
                        MARKET
                    </span>
                </a>
                <x-script-switcher />
            </nav>

            <div class="flex w-full flex-col items-center gap-[16px] px-[16px]">
                {{-- Banner --}}
                <x-banner-carousel :banners="$banners" />

                {{-- Kategoriyalar --}}
                <div class="flex h-[88px] w-full items-center gap-[10px]">
                    <a href="{{ route('category', 'telefonlar') }}" class="flex min-w-0 flex-1 flex-col items-center gap-[10px]">
                        <span class="relative h-[64px] w-full overflow-clip rounded-[16px] bg-[#f6f6f6]">
                            <img src="{{ asset('img/cat-phones.png') }}" alt="" class="absolute top-[8px] left-[10px] size-[89px] object-cover">
                        </span>
                        <span class="text-[13px] leading-normal font-medium whitespace-nowrap text-ink-2">Telefonlar</span>
                    </a>
                    <a href="{{ route('category', 'maishiy-texnika') }}" class="flex min-w-0 flex-1 flex-col items-center gap-[10px]">
                        <span class="relative h-[64px] w-full overflow-clip rounded-[16px] bg-[#f6f6f6]">
                            <img src="{{ asset('img/cat-appl1.png') }}" alt="" class="absolute top-[27px] left-[13.67px] h-[44px] w-[50px] object-cover">
                            <span class="absolute top-[9px] left-[47.67px] h-[70px] w-[55px] overflow-hidden">
                                <img src="{{ asset('img/cat-appl2.png') }}" alt="" class="absolute top-[-24.62%] left-[-41.82%] h-[149.23%] w-[141.82%] max-w-none">
                            </span>
                        </span>
                        <span class="text-[13px] leading-normal font-medium whitespace-nowrap text-ink-2">Maishiy texnika</span>
                    </a>
                    <a href="{{ route('category', 'bolalar-uchun') }}" class="flex min-w-0 flex-1 flex-col items-center gap-[10px]">
                        <span class="relative h-[64px] w-full overflow-clip rounded-[16px] bg-[#f6f6f6]">
                            <span class="absolute top-[11px] left-[16px] h-[72px] w-[78px] overflow-hidden">
                                <img src="{{ asset('img/teddy.png') }}" alt="" class="absolute top-[-21.16%] left-0 h-[145.09%] w-full max-w-none">
                            </span>
                        </span>
                        <span class="text-[13px] leading-normal font-medium whitespace-nowrap text-ink-2">Bolalar uchun</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Faol buyurtma kartasi: to'lov tasdiqlangach ko'rinadi, yetkazilgach yoki buyurtma bo'lmasa yashirin (JS: initActiveOrder) --}}
        <div id="active-order" class="hidden w-full px-[12px]"
             data-statuses='@json(config('catalog.order_statuses'))'
             data-status-colors='@json(config('catalog.status_colors'))'
             data-latest-order-id="{{ $latestActiveOrderModel?->order_number ?? '' }}"
             data-server-orders='@json($serverOrders ?? [])'>
            <a href="{{ route('order', $order['id']) }}" data-order-link class="flex w-full flex-col gap-[10px] overflow-clip rounded-[24px] border border-[#b8d3fe] bg-[#e2edff] p-[16px] shadow-[0_4px_16px_rgba(34,123,252,0.08)]">
                <div class="flex w-full items-center justify-between">
                    <div class="flex flex-col gap-[2px] leading-normal whitespace-nowrap">
                        <p class="text-[15px] font-semibold text-ink" data-order-number>#{{ $order['id'] }}</p>
                        <p class="text-[12px] text-ink-3" data-order-date>{{ $latestActiveOrderModel?->created_at?->format('d.m.Y') ?? '17.11.2026' }}</p>
                    </div>
                    <span data-order-badge class="rounded-[8px] border border-white/80 bg-primary-100 px-[8px] py-[4px] text-[11px] leading-normal font-semibold text-primary">Yetkazilmoqda</span>
                </div>
                {{-- 4 bosqichli progress: statusga qarab bo'yaladi (JS: initActiveOrder) --}}
                <div class="flex w-full gap-[4px]" data-order-bars>
                    @for ($i = 0; $i < 4; $i++)
                        <span class="h-[4px] flex-1 rounded-[2px] bg-white/80 transition-colors duration-300"></span>
                    @endfor
                </div>
                <div class="flex w-full items-center gap-[8px]">
                    <div class="flex items-center gap-[8px]" data-order-images>
                        @foreach ($order['items'] as $item)
                            <img src="{{ asset($item['image']) }}" alt="" class="h-[64px] w-[52px] shrink-0 rounded-[12px] bg-white object-cover">
                        @endforeach
                    </div>
                    <span class="min-w-0 flex-1"></span>
                    <div class="flex flex-col items-end gap-[2px] leading-normal whitespace-nowrap">
                        <p class="text-[12px] text-ink-3" data-order-count>{{ count($order['items']) }} ta mahsulot</p>
                        <p class="text-[16px] font-semibold text-ink" data-order-total>{{ $latestActiveOrderModel?->formatted_total ?? '78 160 ₽' }}</p>
                    </div>
                </div>
                <div class="flex w-full items-center gap-[8px] rounded-[12px] bg-white px-[12px] py-[10px]">
                    <p class="min-w-0 flex-1 text-[12px] leading-normal font-medium text-ink">Kimga: <span data-order-to>Mohira Karimova · Farg‘ona, Marg‘ilon</span></p>
                    <img src="{{ asset('img/chev-sm.svg') }}" alt="" class="size-[16px]">
                </div>
            </a>
        </div>

        @foreach ($categories as $category)
            <x-home-section :category="$category" :products="$byCategory($category)" />
        @endforeach
    </div>
</x-layouts.app>
