@php
    $order = $order ?? config('catalog.order');
    $stepDefs = config('catalog.order_steps');
    $current = $status['step']; // 1..4

    $paidSubtext = isset($orderModel) && $orderModel && $orderModel->created_at
        ? $orderModel->created_at->format('d.m · H:i')
        : '17.11 · 14:20';

    // Timeline:
    // 1-bosqich ("To'landi") to'lov qabul qilingan zahoti bajarilgan (yashil galochka ✓).
    // Keyingi bosqichlar: $n < $current (yoki 4-bosqich yakunlanganda) -> 'done' (yashil galochka ✓),
    // $n === $current -> 'current' (ko'k ikonka), $n > $current -> 'pending' (kulrang ikonka).
    $steps = [];
    foreach ($stepDefs as $n => $title) {
        if ($n === 1 || $n < $current || ($n === $current && $n === 4)) {
            $state = 'done';
        } elseif ($n === $current) {
            $state = 'current';
        } else {
            $state = 'pending';
        }

        if ($n === 4 && $state === 'done') {
            $title = 'Buyurtma yetkazildi';
        }

        $steps[] = [
            'n' => $n,
            'state' => $state,
            'title' => $title,
            'subtitle' => $n === 1 ? $paidSubtext : null,
        ];
    }

    $fmtRub = fn (int $amount) => number_format($amount, 0, '.', ' ').' ₽';

    if (isset($orderModel) && $orderModel) {
        $rows = [
            ['Tovarlar', $fmtRub($orderModel->items_total_rub), 'text-ink'],
        ];
        if ($orderModel->discount_rub > 0) {
            $rows[] = ['Chegirma', '−'.$fmtRub($orderModel->discount_rub), 'text-success'];
        }
        if ($orderModel->oversize_fee_rub > 0) {
            $rows[] = ['Katta texnika', $fmtRub($orderModel->oversize_fee_rub), 'text-ink'];
        }
        $rows[] = [
            'Yetkazish',
            $orderModel->delivery_fee_rub > 0 ? $fmtRub($orderModel->delivery_fee_rub) : 'Bepul',
            $orderModel->delivery_fee_rub > 0 ? 'text-ink' : 'text-success',
        ];
        $totalFormatted = $orderModel->formatted_total;
        $recipientLine = $orderModel->recipient_name.' · '.$orderModel->recipient_phone;
        $districtClean = preg_replace('/ (shahri|tumani)$/', '', (string) $orderModel->district);
        $addressParts = array_filter([$orderModel->region, $districtClean, $orderModel->address]);
        $addressLine = implode(', ', $addressParts).($orderModel->landmark ? ' · '.$orderModel->landmark : '');
        $wishText = $orderModel->wish;
    } else {
        $rows = [
            ['Tovarlar', '89 960 ₽', 'text-ink'],
            ['Chegirma', '−13 000 ₽', 'text-success'],
            ['Katta texnika', '990 ₽', 'text-ink'],
            ['Yetkazish', 'Bepul', 'text-success'],
        ];
        $totalFormatted = '77 950 ₽';
        $recipientLine = 'Mohira Karimova · +998 90 123-45-67';
        $addressLine = 'Farg‘ona, Marg‘ilon, Navro‘z mahallasi, Bog‘ ko‘chasi 12-uy · 15-maktab yonida';
        $wishText = 'Onajon, tug‘ilgan kuningiz bilan! Sog‘ bo‘ling';
    }

    $note = $status['note'];
    $noteStyles = [
        'info' => 'bg-[rgba(34,123,252,0.1)]',
        'courier' => 'bg-[rgba(34,123,252,0.1)]',
        'success' => 'bg-[rgba(18,183,106,0.1)]',
        'danger' => 'bg-[rgba(240,68,56,0.08)]',
    ];
@endphp

<x-layouts.app :title="'Buyurtma #'.$order['id'].' — Yubor Market'">
    <div class="flex w-full flex-col pb-[120px]">
        <header class="sticky top-0 z-20 flex w-full items-center gap-[12px] bg-page px-[16px] pt-[10px] pb-[12px]">
            <a href="{{ route('home') }}" class="size-[24px] shrink-0" aria-label="Orqaga">
                <img src="{{ asset('img/icon-back.svg') }}" alt="" class="size-full">
            </a>
            <h1 class="min-w-0 flex-1 text-[18px] leading-normal font-semibold text-ink">Buyurtma #{{ $order['id'] }}</h1>
            <x-script-switcher />
        </header>

        <div class="flex w-full flex-col gap-[12px] px-[16px] pt-[8px]">
            {{-- Holat --}}
            <section class="flex w-full flex-col gap-[14px] overflow-clip rounded-[20px] bg-white p-[16px]"
                     data-order-status="{{ $statusKey }}"
                     @if (isset($orderModel) && $orderModel)
                         data-order-recipient="{{ $orderModel->recipient_name }}"
                         data-order-region="{{ $orderModel->region }}"
                         data-order-district="{{ $orderModel->district }}"
                     @endif>
                <div class="flex w-full items-center justify-between gap-[12px]">
                    <div class="flex min-w-0 flex-col gap-[2px] leading-normal">
                        <p class="text-[17px] font-semibold text-ink">{{ $status['title'] }}</p>
                        <p class="text-[13px] text-ink-3">{{ $status['subtitle'] }}</p>
                    </div>
                    <span class="shrink-0 rounded-[8px] px-[8px] py-[4px] text-[12px] leading-normal font-semibold whitespace-nowrap"
                          style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                        {{ $current.' / 4' }}
                    </span>
                </div>

                <ol class="flex w-full flex-col">
                    @foreach ($steps as $step)
                        <li class="flex w-full items-stretch gap-[12px]">
                            <div class="flex shrink-0 flex-col items-center">
                                <span @class([
                                    'relative flex size-[32px] shrink-0 items-center justify-center rounded-[16px]',
                                    'bg-success text-white' => $step['state'] === 'done',
                                    'bg-primary text-white' => $step['state'] === 'current',
                                    'bg-[#f6f6f6] text-[#a3a3a3]' => $step['state'] === 'pending',
                                ])>
                                    <x-step-icon :step="$step['n']" :state="$step['state']" />
                                </span>
                                @unless ($loop->last)
                                    <span class="min-h-[22px] w-[2px] flex-1 {{ $step['state'] === 'done' ? 'bg-success' : 'bg-line' }}"></span>
                                @endunless
                            </div>
                            <div class="flex min-w-0 flex-col justify-center {{ $loop->last ? 'pt-[6px]' : 'pt-[6px] pb-[14px]' }}">
                                <p @class([
                                    'text-[14px] leading-normal',
                                    'font-semibold text-ink' => in_array($step['state'], ['done', 'current']),
                                    'font-medium text-ink-2' => $step['state'] === 'pending',
                                ])>{{ $step['title'] }}</p>
                                @if (!empty($step['subtitle']))
                                    <p class="mt-[2px] text-[12px] leading-normal text-ink-3">{{ $step['subtitle'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Oluvchi --}}
            <section class="flex w-full flex-col gap-[12px] overflow-clip rounded-[20px] bg-white p-[16px]">
                <h2 class="text-[16px] leading-normal font-semibold text-ink">Oluvchi</h2>
                <div class="flex w-full items-center gap-[12px]">
                    <div class="relative size-[40px] shrink-0 rounded-[12px] bg-[rgba(34,123,252,0.1)]">
                        <img src="{{ asset('img/icon-pin-blue.svg') }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
                    </div>
                    <div class="flex min-w-0 flex-col gap-[2px]">
                        <p class="text-[14px] leading-normal font-medium text-ink">{{ $recipientLine }}</p>
                        <p class="max-w-[255px] text-[12px] leading-[16px] text-ink-3">{{ $addressLine }}</p>
                    </div>
                </div>
                @if ($wishText)
                    <div class="w-full rounded-[12px] bg-[#f6f7f9] px-[12px] py-[10px]">
                        <p class="max-w-[287px] text-[12px] leading-[16px] text-ink-2">Tabrik xati: «{{ $wishText }}»</p>
                    </div>
                @endif
            </section>

            {{-- Tovarlar --}}
            <section class="flex w-full flex-col gap-[12px] overflow-clip rounded-[20px] bg-white p-[16px]">
                <h2 class="text-[16px] leading-normal font-semibold text-ink">Tovarlar · {{ count($order['items']) }}</h2>
                @foreach ($order['items'] as $item)
                    <div class="flex w-full items-center gap-[12px]">
                        <button type="button" data-open-viewer="{{ $loop->index }}" aria-label="{{ $item['title'] }} — rasmni kattalashtirish"
                                class="shrink-0 cursor-zoom-in rounded-[12px] transition active:scale-95">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="h-[64px] w-[52px] rounded-[12px] bg-[#f6f7f9] object-cover">
                        </button>
                        <div class="flex min-w-0 flex-1 flex-col gap-[2px] leading-normal whitespace-nowrap">
                            <p class="text-[13px] font-medium text-ink">{{ $item['title'] }}</p>
                            <p class="text-[12px] text-ink-3">{{ $item['qty'] }}</p>
                        </div>
                        <p class="text-[14px] leading-normal font-semibold whitespace-nowrap text-ink">{{ $item['price'] }}</p>
                    </div>
                @endforeach
            </section>

            {{-- To'lov --}}
            <section class="flex w-full flex-col gap-[10px] overflow-clip rounded-[20px] bg-white p-[16px] leading-normal whitespace-nowrap">
                <h2 class="text-[16px] font-semibold text-ink">To‘lov</h2>
                @foreach ($rows as [$label, $value, $color])
                    <div class="flex w-full items-center justify-between text-[14px]">
                        <span class="text-ink-3">{{ $label }}</span>
                        <span class="font-medium {{ $color }}">{{ $value }}</span>
                    </div>
                @endforeach
                <div class="flex w-full items-center justify-between text-[16px] text-ink">
                    <span class="font-semibold">Jami to‘landi</span>
                    <span class="font-bold">{{ $totalFormatted }}</span>
                </div>
            </section>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="bottom-dock flex flex-col gap-[10px] rounded-t-[24px] bg-white px-[16px] pt-[12px] shadow-[0px_-4px_24px_0px_rgba(26,51,102,0.08)]">
        @if ($statusKey === 'delivered')
            <a href="{{ route('home') }}"
               class="flex h-[47px] w-full items-center justify-center rounded-[14px] bg-[#267efd] px-[20px] py-[12px] text-[16px] leading-[24px] font-medium text-white">
                Yana sovg‘a yuborish
            </a>
        @endif
        <a href="{{ !empty($settings['support_chat_url']) ? $settings['support_chat_url'] : 'https://t.me/yubormarket_support' }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center rounded-[14px] border border-primary px-[20px] py-[16px] text-[16px] leading-[24px] font-medium whitespace-nowrap text-primary">
            Yordam chat
        </a>
    </div>

    {{-- Tovar rasmlarini kattalashtirib ko'rish --}}
    @push('modals')
        <x-image-viewer :share="false"
            :images="collect($order['items'])->map(fn ($item) => ['src' => $item['image'], 'alt' => $item['title']])" />
    @endpush

    @if ($success)
        @push('modals')
            <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="success-title">
                <div class="absolute inset-0 bg-[rgba(13,20,38,0.45)]"></div>
                <div class="relative flex w-[345px] max-w-[calc(100%-30px)] flex-col items-center gap-[16px] overflow-clip rounded-[32px] bg-white px-[16px] py-[20px]">
                    <div class="flex flex-col items-center gap-[8px]">
                        <div class="relative size-[64px] rounded-[32px] bg-[rgba(18,183,106,0.1)]">
                            <img src="{{ asset('img/icon-check-big.svg') }}" alt="" class="absolute top-[16px] left-[16px] size-[32px]">
                        </div>
                        <div class="flex flex-col items-center gap-[8px]">
                            <p id="success-title" class="text-[20px] leading-normal font-semibold whitespace-nowrap text-ink">To‘lov qabul qilindi!</p>
                            <p class="w-[280px] max-w-full text-center text-[13px] leading-[18px] text-ink-3">{{ isset($orderModel) && $orderModel ? $orderModel->recipient_name.'ga' : 'Mohira opaga' }} SMS yuborildi. Kuryer yetkazishdan oldin qo‘ng‘iroq qiladi.</p>
                        </div>
                    </div>
                    <a href="{{ route('order', ['id' => $order['id']]) }}"
                       class="flex h-[47px] w-full items-center justify-center rounded-[14px] bg-[#267efd] px-[20px] py-[12px] text-[16px] leading-[24px] font-medium text-white">
                        Tushunarli
                    </a>
                </div>
            </div>
        @endpush
    @endif
</x-layouts.app>
