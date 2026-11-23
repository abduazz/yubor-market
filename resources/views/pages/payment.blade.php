@php
    $rateCard = $settings['rate_card_uzs'] ?? '158.00';
    $rateSbp = $settings['rate_sbp_uzs'] ?? '156.250';
    $productSlug = $selectedProduct['slug'] ?? request()->query('product');
    $giftWrapFee = (int) ($settings['gift_wrap_fee_rub'] ?? 190);
    $oversizeFee = (int) ($settings['oversize_fee_rub'] ?? 990);

    if (! empty($selectedProduct)) {
        $thumbs = [$selectedProduct['gallery'] ?? $selectedProduct['image']];
        $extraCount = 0;
        $itemsCount = 1;
        $itemsTotalRub = (int) ($selectedProduct['price_rub'] ?? preg_replace('/\D/', '', (string) ($selectedProduct['price'] ?? 0)));
        $appliedOversize = ($selectedProduct['category'] ?? '') === 'maishiy-texnika' ? $oversizeFee : 0;
        $totalRub = $itemsTotalRub + $giftWrapFee + $appliedOversize;
    } else {
        $thumbs = ['img/gallery-a55.png', 'img/thumb-2.png', 'img/thumb-3.png'];
        $extraCount = 1;
        $itemsCount = 4;
        $itemsTotalRub = 76980;
        $appliedOversize = $oversizeFee;
        $totalRub = 78160;
    }

    $fmtRub = fn (int $amount) => number_format($amount, 0, '.', ' ').' ₽';
@endphp

<x-layouts.app title="Sovg‘a va to‘lov — Yubor Market">
    <form action="{{ route('checkout.processing') }}" method="POST" class="flex w-full flex-col gap-[16px] pb-[110px]">
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
                    <textarea name="wish" placeholder="Yozing" maxlength="300"
                              class="w-full flex-1 resize-none bg-transparent text-[16px] leading-[24px] text-[#444] outline-none placeholder:text-muted"></textarea>
                </label>
            </section>

            {{-- O'tkazma usuli --}}
            <section class="flex w-full flex-col gap-[16px] overflow-clip rounded-[24px] bg-white p-[16px]">
                <p class="text-[16px] leading-normal font-semibold whitespace-nowrap text-ink">O‘tkazma usulini tanlang</p>
                <div class="flex w-full flex-col gap-[12px]">
                    @foreach ([
                        ['value' => 'card', 'title' => 'Bank kartasi orqali o‘tkazish', 'rate' => '1 RUB = '.$rateCard.' UZS'],
                        ['value' => 'sbp', 'title' => 'SBP orqali o‘tkazish', 'rate' => '1 RUB = '.$rateSbp.' UZS'],
                    ] as $i => $method)
                        <label class="flex w-full cursor-pointer items-center justify-between overflow-clip rounded-[16px] bg-page px-[14px] py-[12px]">
                            <span class="flex items-center gap-[11px]">
                                <span class="relative size-[42px] shrink-0 overflow-clip rounded-[10px] bg-white">
                                    @if ($method['value'] === 'card')
                                        <img src="{{ asset('img/icon-card.svg') }}" alt="" class="absolute top-[calc(50%+0.5px)] left-1/2 size-[24px] -translate-x-1/2 -translate-y-1/2">
                                    @else
                                        <span class="absolute top-1/2 left-1/2 h-[21px] w-[36px] -translate-x-1/2 -translate-y-1/2 overflow-hidden">
                                            <img src="{{ asset('img/sbp-logo.png') }}" alt="SBP" class="absolute top-[-48.14%] left-[-43.5%] h-[197.71%] w-[185.93%] max-w-none">
                                        </span>
                                    @endif
                                </span>
                                <span class="flex flex-col gap-[4px] text-[14px] whitespace-nowrap">
                                    <span class="leading-normal font-medium text-ink">{{ $method['title'] }}</span>
                                    <span class="flex gap-[6px]">
                                        <span class="leading-normal text-ink-3">Kurs:</span>
                                        <span class="leading-[20px] font-medium text-primary">{{ $method['rate'] }}</span>
                                    </span>
                                </span>
                            </span>
                            <input type="radio" name="method" value="{{ $method['value'] }}" class="peer sr-only" @checked($i === 0)>
                            <span class="relative size-[20px] shrink-0 rounded-full border-[1.5px] border-[#c8d3df] bg-white peer-checked:border-[6px] peer-checked:border-primary"></span>
                        </label>
                    @endforeach
                </div>
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
                        @if ($giftWrapFee > 0)
                            <div class="flex w-full items-center justify-between">
                                <span class="text-ink-3">Sovg‘a qadog‘i</span>
                                <span class="font-medium text-ink">{{ $fmtRub($giftWrapFee) }}</span>
                            </div>
                        @endif
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
                <button type="submit"
                        class="flex items-center justify-center rounded-[14px] bg-primary px-[20px] py-[12px] text-[16px] leading-[24px] font-medium whitespace-nowrap text-white transition active:scale-[.99]">
                    To‘lovga o‘tish
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
