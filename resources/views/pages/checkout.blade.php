@php
    $regions = $regions ?? config('catalog.regions');
    $districts = $districts ?? config('catalog.districts');
    $deliveryBadgeText = $settings['delivery_badge_text'] ?? 'Yetkazish bepul · 2–3 kun (EMU)';
    $productSlug = $selectedProduct['slug'] ?? request()->query('product');
@endphp

<x-layouts.app title="Buyurtmani rasmiylashtirish — Yubor Market">
    <form id="checkout-form" action="{{ route('checkout.payment', array_filter(['product' => $productSlug])) }}" method="GET" novalidate
          class="flex w-full flex-col gap-[16px] pb-[110px]">
        <input type="hidden" name="product_slug" value="{{ $productSlug }}">
        <x-nav title="Buyurtmani rasmiylashtirish" :back="url()->previous()" :steps="1" />

        <div class="flex w-full flex-col gap-[16px] px-[16px]">
            {{-- Yuboruvchi --}}
            <section class="flex w-full flex-col gap-[16px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <x-card-title icon="img/icon-user.svg">Siz — yuboruvchi (Rossiya)</x-card-title>
                <div class="flex w-full flex-col gap-[12px]">
                    <x-field label="Ismingiz" name="sender_name" placeholder="Yuboruvchi ismi" autocomplete="name"
                             required rule="name" maxlength="60" />
                    <x-field label="Telefon raqamingiz" name="sender_phone" type="tel" inputmode="tel"
                             prefix="+7" placeholder="___ ___ - __ - __" data-mask="ru" required rule="phone-ru" autocomplete="tel" />
                </div>
            </section>

            {{-- Oluvchi --}}
            <section class="flex w-full flex-col gap-[16px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <x-card-title icon="img/icon-gift.svg">Oluvchi (O‘zbekiston)</x-card-title>
                <div class="flex w-full flex-col gap-[12px]">
                    <x-field label="Oluvchining ismi" name="recipient_name" placeholder="Ismini yozing"
                             required rule="name" maxlength="60" />
                    <x-field label="Telefon raqami" name="recipient_phone" type="tel" inputmode="tel"
                             prefix="+998" placeholder="__ ___ - __ - __" data-mask="uz" required rule="phone-uz" />
                    <x-field label="Qo‘shimcha raqam (ixtiyoriy)" name="recipient_phone_2" type="tel" inputmode="tel"
                             prefix="+998" placeholder="__ ___ - __ - __" data-mask="uz" rule="phone-uz" />
                </div>
            </section>

            {{-- Manzil --}}
            <section class="flex w-full flex-col gap-[16px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <x-card-title icon="img/icon-pin.svg">Yetkazish manzili</x-card-title>
                <div class="flex w-full flex-col gap-[12px]">
                    <x-select-field label="Viloyat" name="region" placeholder="Viloyatni tanlang" sheet="region-sheet"
                                    required message="Viloyatni tanlang" />
                    <x-select-field label="Tuman / Shahar" name="district" placeholder="Tuman yoki shaharni tanlang" sheet="district-sheet"
                                    required message="Tuman yoki shaharni tanlang" />
                    <x-field label="Mahalla, ko‘cha, uy" name="address" placeholder="Mahalla, ko‘cha nomi, uy raqami"
                             required rule="address" maxlength="120" />
                    <x-field label="Mo‘jal (ixtiyoriy)" name="landmark" placeholder="Masalan: 15-maktab yonida" maxlength="200" multiline />
                </div>
                <div id="delivery-note" class="hidden w-full items-center justify-center gap-[8px] rounded-[16px] bg-[rgba(18,183,106,0.05)] px-[12px] py-[14px]">
                    <img src="{{ asset('img/icon-truck-green.svg') }}" alt="" class="size-[18px]">
                    <span class="text-[13px] leading-normal font-medium whitespace-nowrap text-success-dark" data-delivery-text>{{ $deliveryBadgeText }}</span>
                </div>
            </section>
        </div>

        <div class="bottom-dock rounded-t-[24px] bg-white px-[12px] pt-[10px]">
            {{-- To'ldirilmaguncha o'chiq (kulrang); hammasi to'g'ri bo'lsa ko'k bo'ladi --}}
            <button type="submit" disabled data-submit
                    class="flex w-full items-center justify-center rounded-[14px] bg-primary px-[20px] py-[16px] text-[16px] leading-[24px] font-medium text-white transition active:scale-[.99]
                           disabled:cursor-not-allowed disabled:bg-[#dbe4ee] disabled:text-[#a3a3a3] disabled:active:scale-100">
                Davom etish
            </button>
        </div>
    </form>

    @push('modals')
        <x-bottom-sheet id="region-sheet" title="Viloyatni tanlang" :items="$regions" icon search-variant="region" />
        <x-bottom-sheet id="district-sheet" title="Tuman / shahar" :items="[]" search-variant="district" />
        <script>window.__DISTRICTS__ = @json($districts);</script>
    @endpush
</x-layouts.app>
