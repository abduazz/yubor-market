@php
    $specs = $product['specs'] ?? config('catalog.specs');
    $description = $product['description'] ?? 'Galaxy A55 — metall korpus, 120 Hz yorqin ekran va kechasi ham aniq suratga oladigan 50 MP kamera. Ota-onangiz uchun qulay: katta shrift rejimi va 2 kunlik batareya. Qurilma IP67 standartida suv va changdan himoyalangan, 4 yil davomida Android yangilanishlari kafolatlanadi.';
    $images = $product['images'] ?? [$product['gallery']];
    $slides = count($images);
@endphp

<x-layouts.app :title="$product['full_title'].' — Yubor Market'">
    <div class="flex w-full flex-col gap-[10px] pb-[110px]">

        {{-- Galereya --}}
        <div class="relative h-[470px] w-full overflow-clip rounded-b-[24px] bg-[#f6f7f9]">
            <div id="gallery" class="no-scrollbar flex h-full w-full snap-x snap-mandatory overflow-x-auto">
                @foreach ($images as $i => $image)
                    <button type="button" data-open-viewer="{{ $i }}" aria-label="Rasmni kattalashtirish {{ $i + 1 }}"
                            class="h-full w-full shrink-0 snap-center snap-always">
                        <img src="{{ asset($image) }}" alt="{{ $product['full_title'] }}" draggable="false"
                             @if ($i > 0) loading="lazy" @endif
                             class="h-full w-full object-cover select-none">
                    </button>
                @endforeach
            </div>

            <a href="{{ $backUrl ?? route('home') }}" aria-label="Orqaga"
               class="absolute top-[12px] left-[16px] size-[40px] overflow-clip rounded-[20px] bg-[rgba(255,255,255,0.92)]">
                <img src="{{ asset('img/btn-back.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
            </a>
            <div class="absolute top-[18px] right-[66px]">
                <x-script-switcher variant="glass" />
            </div>
            <button type="button" aria-label="Ulashish" data-share
                    class="absolute top-[12px] right-[16px] size-[40px] overflow-clip rounded-[20px] bg-[rgba(255,255,255,0.92)]">
                <img src="{{ asset('img/btn-share.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
            </button>

            <div class="absolute bottom-[16px] left-[16px] rounded-[10px] bg-ink px-[10px] py-[4px] text-[12px] leading-normal font-medium whitespace-nowrap text-white">
                <span data-gallery-index>1</span> / {{ $slides }}
            </div>
            <div class="absolute bottom-[22px] left-1/2 flex -translate-x-1/2 items-start gap-[5px]" data-gallery-dots>
                @for ($i = 0; $i < $slides; $i++)
                    <span class="h-[6px] rounded-[3px] transition-all {{ $i === 0 ? 'w-[16px] bg-primary' : 'w-[6px] bg-[#c8d3df]' }}"></span>
                @endfor
            </div>
        </div>

        {{-- Narx --}}
        <section class="flex w-full flex-col gap-[10px] overflow-clip rounded-[24px] bg-white p-[16px]">
            <p class="text-[28px] leading-normal font-semibold whitespace-nowrap text-ink">{{ $product['price'] }}</p>
            <h1 class="w-full text-[18px] leading-[24px] font-medium text-ink">{{ $product['full_title'] }}</h1>
            <div class="flex w-full items-center gap-[6px]">
                <span class="text-[14px] leading-normal font-semibold text-primary">{{ $product['brand'] }}</span>
                <img src="{{ asset('img/icon-verified.svg') }}" alt="" class="size-[14px]">
                <span class="text-[12px] leading-normal font-medium text-ink-3">Original</span>
            </div>
            <div class="flex w-full items-center gap-[8px] rounded-[12px] bg-[rgba(18,183,106,0.1)] px-[12px] py-[10px]">
                <img src="{{ asset('img/icon-shield.svg') }}" alt="" class="size-[16px]">
                <span class="text-[12px] leading-normal font-medium whitespace-nowrap text-success-dark">{{ $product['warranty_text'] ?? 'Rasmiy kafolat 12 oy · Original mahsulot' }}</span>
            </div>
        </section>

        {{-- Xususiyatlar --}}
        <section class="flex w-full flex-col gap-[10px] overflow-clip rounded-[24px] bg-white p-[16px]">
            <div class="flex w-full items-center justify-between">
                <h2 class="text-[17px] leading-normal font-semibold text-ink">Xususiyatlar</h2>
                <a href="{{ route('product.specs', $product['slug']) }}" class="flex items-center gap-[2px]">
                    <span class="text-[13px] leading-normal font-medium text-primary">Barchasi</span>
                    <img src="{{ asset('img/chev-blue-sm.svg') }}" alt="" class="size-[14px]">
                </a>
            </div>
            @foreach ($specs as $label => $value)
                <x-spec-row :label="$label" :value="$value" />
            @endforeach
        </section>

        {{-- Tavsif --}}
        <section class="flex w-full flex-col gap-[8px] overflow-clip rounded-[24px] bg-white p-[16px]" data-collapsible>
            <h2 class="text-[17px] leading-normal font-semibold text-ink">Tavsif</h2>
            <p class="line-clamp-4 text-[14px] leading-[20px] text-ink" data-collapsible-text>
                {{ $description }}
            </p>
            <button type="button" class="self-start text-[14px] leading-normal font-medium text-primary" data-collapsible-toggle>Batafsil</button>
        </section>

        {{-- Yetkazish --}}
        <section class="flex w-full flex-col gap-[14px] overflow-clip rounded-[24px] bg-white p-[16px]">
            <h2 class="text-[17px] leading-normal font-semibold text-ink">Yetkazish</h2>
            <div class="flex w-full items-center gap-[12px]">
                <div class="relative size-[40px] shrink-0 rounded-[12px] bg-[rgba(34,123,252,0.1)]">
                    <img src="{{ asset('img/icon-truck.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
                </div>
                <div class="flex min-w-0 flex-1 flex-col gap-[2px] leading-normal">
                    <p class="text-[14px] font-medium text-ink">O‘zbekiston bo‘ylab bepul</p>
                    <p class="text-[12px] text-ink-3">{{ $settings['delivery_provider_text'] ?? '2-4 kunda EMU po‘chta orqali' }}</p>
                </div>
            </div>
        </section>
    </div>

    {{-- Figma: "20 · Tovar — rasm galereyasi" (to'liq ekran) --}}
    @push('modals')
        <x-image-viewer :images="$images" :alt="$product['full_title']" />
        <x-share-sheet :title="$product['full_title']" :price="$product['price']" :image="$product['image']"
                       :text="$product['full_title'].' — '.$product['price'].'. Yubor Market’da ko‘ring:'" />
    @endpush

    {{-- Pastki panel (sticky) --}}
    <div class="bottom-dock rounded-t-[24px] bg-white px-[16px] pt-[12px] shadow-[0px_-4px_24px_0px_rgba(26,51,102,0.1)]">
        <a href="{{ route('checkout', ['product' => $product['slug']]) }}"
           class="flex h-[56px] w-full items-center justify-center rounded-[14px] bg-primary p-[20px] text-center text-[16px] leading-[24px] font-medium text-white transition active:scale-[.99]">
            Sotib olish
        </a>
    </div>
</x-layouts.app>
