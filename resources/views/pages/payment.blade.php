@php
    $productSlug = $selectedProduct['slug'] ?? request()->query('product') ?? request()->query('product_slug');
    $oversizeFee = (int) ($settings['oversize_fee_rub'] ?? 990);

    if (! empty($selectedProduct)) {
        $thumbs = [$selectedProduct['gallery'] ?? $selectedProduct['image']];
        $extraCount = 0;
        $itemsCount = 1;
        $itemsTotalRub = (int) ($selectedProduct['price_rub'] ?? preg_replace('/\D/', '', (string) ($selectedProduct['price'] ?? 0)));
        $appliedOversize = ($selectedProduct['category'] ?? '') === 'maishiy-texnika' ? $oversizeFee : 0;
        $totalRub = $itemsTotalRub + $appliedOversize;
    } else {
        $thumbs = ['img/gallery-a55.png', 'img/thumb-2.png', 'img/thumb-3.png'];
        $extraCount = 1;
        $itemsCount = 4;
        $itemsTotalRub = 76980;
        $appliedOversize = $oversizeFee;
        $totalRub = 77970;
    }

    $fmtRub = fn (int $amount) => number_format($amount, 0, '.', ' ').' ₽';
@endphp

<x-layouts.app title="Sovg‘a va to‘lov — Yubor Market">
    <form id="payment-form" action="{{ route('checkout.processing') }}" method="POST" class="flex w-full flex-col gap-[16px] pb-[110px]">
        @csrf
        <input type="hidden" name="product_slug" value="{{ $productSlug }}" data-checkout-field="product_slug">
        <input type="hidden" name="sender_name" data-checkout-field="sender_name">
        <input type="hidden" name="sender_phone" data-checkout-field="sender_phone">
        <input type="hidden" name="recipient_name" data-checkout-field="recipient_name">
        <input type="hidden" name="recipient_phone" data-checkout-field="recipient_phone">
        <input type="hidden" name="recipient_phone_2" data-checkout-field="recipient_phone_2">
        <input type="hidden" name="region" data-checkout-field="region">
        <input type="hidden" name="district" data-checkout-field="district">
        <input type="hidden" name="address" data-checkout-field="address">
        <input type="hidden" name="landmark" data-checkout-field="landmark">
        <input type="hidden" name="method" id="payment-method-input" value="card">

        <x-nav title="Buyurtmani rasmiylashtirish" :back="route('checkout', array_filter(['product' => $productSlug]))" :steps="3" />

        <div class="flex w-full flex-col gap-[16px] px-[16px]">
            {{-- Kimga --}}
            <a href="{{ route('checkout', array_filter(['product' => $productSlug])) }}" class="flex w-full flex-col overflow-clip rounded-[24px] bg-white px-[16px] py-[14px]">
                <div class="flex w-full items-center gap-[12px]">
                    <div class="relative size-[40px] shrink-0 overflow-clip rounded-[12px] bg-[#e8f1fe]">
                        <img src="{{ asset('img/icon-home.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-[4px] leading-normal whitespace-nowrap">
                        <p class="text-[14px] font-medium text-ink" data-summary="phone">+998 90 123-45-67</p>
                        <p class="truncate text-[12px] text-ink-3" data-summary="address">Farg‘ona, Marg‘ilon, Navro‘z mahallasi, 12-uy</p>
                    </div>
                    <img src="{{ asset('img/chev-gray.svg') }}" alt="" class="size-[18px] shrink-0">
                </div>
            </a>

            {{-- Tilak --}}
            <section class="flex w-full flex-col gap-[16px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <div class="flex items-center gap-[10px]">
                    <div class="relative size-[40px] shrink-0 overflow-clip rounded-[12px] bg-[#e8f1fe]">
                        <img src="{{ asset('img/icon-gift-blue.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
                    </div>
                    <p class="text-[16px] leading-normal font-semibold whitespace-nowrap text-ink">Yaqiningiz uchun tilak</p>
                </div>
                <label class="flex h-[120px] w-full cursor-text flex-col gap-[2px] overflow-clip rounded-[16px] bg-field px-[14px] py-[12px] focus-within:ring-2 focus-within:ring-primary/30">
                    <span class="text-[12px] leading-[16px] text-ink-2">Eng yaxshi tilaklar (kuryer qog‘ozda topshiradi)</span>
                    <textarea name="wish" data-wish-input placeholder="Yozing" maxlength="300"
                              class="w-full flex-1 resize-none bg-transparent text-[16px] leading-[24px] text-[#444] outline-none placeholder:text-muted"></textarea>
                </label>
            </section>

            {{-- Narx --}}
            <section class="flex w-full flex-col gap-[12px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <div class="flex w-full flex-col gap-[10px]">
                    <div class="flex items-start">
                        @foreach ($thumbs as $thumb)
                            <span class="relative mr-[-12px] size-[42px] shrink-0 overflow-clip rounded-[14.04px] border-2 border-white bg-[#f8f7f5]">
                                <img src="{{ asset($thumb) }}" alt="" class="absolute top-[calc(50%-0.5px)] left-1/2 h-[29px] w-[24px] -translate-x-1/2 -translate-y-1/2 object-cover">
                            </span>
                        @endforeach
                        @if ($extraCount > 0)
                            <span class="relative size-[42px] shrink-0 overflow-clip rounded-[14.04px] border-2 border-white bg-[#e3edfe]">
                                <span class="absolute top-[10px] left-[11px] text-[14px] leading-normal font-medium text-primary">+{{ $extraCount }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="flex w-full flex-col gap-[10px] text-[14px] leading-normal whitespace-nowrap">
                        <div class="flex w-full items-center justify-between">
                            <span class="text-ink-3">Mahsulotlar ({{ $itemsCount }})</span>
                            <span class="font-medium text-ink">{{ $fmtRub($itemsTotalRub) }}</span>
                        </div>
                        @if ($appliedOversize > 0)
                            <div class="flex w-full items-center justify-between">
                                <span class="text-ink-3">Katta texnika</span>
                                <span class="font-medium text-ink">{{ $fmtRub($appliedOversize) }}</span>
                            </div>
                        @endif
                        <div class="flex w-full items-center justify-between">
                            <span class="text-ink-3">Yetkazish (<span data-summary="region">Farg‘ona</span>)</span>
                            <span class="font-medium text-success">Bepul</span>
                        </div>
                    </div>
                </div>
                <div class="flex w-full items-center justify-between leading-normal font-semibold whitespace-nowrap">
                    <span class="text-[16px] text-ink">Jami</span>
                    <span class="text-[18px] text-primary">{{ $fmtRub($totalRub) }}</span>
                </div>
            </section>
        </div>

        {{-- Bottom bar --}}
        <div class="bottom-dock rounded-t-[24px] bg-white px-[16px] pt-[12px] shadow-[0px_-4px_24px_0px_rgba(26,51,102,0.08)]">
            <div class="flex w-full items-center justify-between pb-[6px]">
                <div class="flex flex-col gap-[4px] leading-normal whitespace-nowrap">
                    <span class="text-[12px] text-ink-3">To‘lov summasi</span>
                    <span class="text-[18px] font-semibold text-ink">{{ $fmtRub($totalRub) }}</span>
                </div>
                <button type="button" data-open-payment-sheet
                        class="flex items-center justify-center rounded-[14px] bg-primary px-[20px] py-[12px] text-[16px] leading-[24px] font-medium whitespace-nowrap text-white transition active:scale-[.99]">
                    To‘lovga o‘tish
                </button>
            </div>
        </div>

        {{-- Bottom sheet: O'tkazma usulini tanlang --}}
        <div class="sheet fixed inset-0 z-50" id="payment-method-sheet" data-open="false" aria-hidden="true">
            <div class="sheet-overlay absolute inset-0 bg-[rgba(13,20,38,0.45)]" data-close-payment-sheet></div>

            <div role="dialog" aria-modal="true" aria-labelledby="payment-method-sheet-title"
                 class="sheet-panel absolute bottom-0 left-1/2 flex w-full max-w-[430px] flex-col gap-[16px] overflow-clip rounded-t-[24px] bg-white px-[16px] pt-[10px]"
                 style="padding-bottom: max(env(safe-area-inset-bottom), 28px)">
                <div class="flex w-full justify-center">
                    <span class="h-[5px] w-[36px] rounded-[3px] bg-line"></span>
                </div>

                <p id="payment-method-sheet-title" class="text-[18px] leading-normal font-semibold whitespace-nowrap text-ink">
                    O‘tkazma usulini tanlang
                </p>

                <div class="flex w-full flex-col gap-[10px]">
                    <button type="button" data-select-payment-method="card"
                            class="flex w-full items-center justify-between overflow-clip rounded-[16px] bg-page px-[14px] py-[12px] text-left transition active:scale-[.99]">
                        <span class="flex items-center gap-[12px]">
                            <span class="relative size-[42px] shrink-0 overflow-clip rounded-[10px] bg-white">
                                <img src="{{ asset('img/icon-card.svg') }}" alt="" class="absolute top-[calc(50%+0.5px)] left-1/2 size-[24px] -translate-x-1/2 -translate-y-1/2">
                            </span>
                            <span class="text-[15px] leading-normal font-medium whitespace-nowrap text-ink">Bank kartasi orqali o‘tkazish</span>
                        </span>
                        <img src="{{ asset('img/chev-blue.svg') }}" alt="" class="size-[18px] shrink-0">
                    </button>

                    <button type="button" data-select-payment-method="sbp"
                            class="flex w-full items-center justify-between overflow-clip rounded-[16px] bg-page px-[14px] py-[12px] text-left transition active:scale-[.99]">
                        <span class="flex items-center gap-[12px]">
                            <span class="relative size-[42px] shrink-0 overflow-clip rounded-[10px] bg-white">
                                <span class="absolute top-1/2 left-1/2 h-[21px] w-[36px] -translate-x-1/2 -translate-y-1/2 overflow-hidden">
                                    <img src="{{ asset('img/sbp-logo.png') }}" alt="SBP" class="absolute top-[-48.14%] left-[-43.5%] h-[197.71%] w-[185.93%] max-w-none">
                                </span>
                            </span>
                            <span class="text-[15px] leading-normal font-medium whitespace-nowrap text-ink">SBP orqali o‘tkazish</span>
                        </span>
                        <img src="{{ asset('img/chev-blue.svg') }}" alt="" class="size-[18px] shrink-0">
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
