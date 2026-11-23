<x-layouts.app title="To‘lov tekshirilmoqda — Yubor Market" screen-class="!bg-white">
    <div class="flex w-full flex-col items-center gap-[20px] px-[32px] pt-[150px]"
         data-redirect="{{ route('order', ['id' => $orderNumber ?? config('catalog.order.id'), 'success' => 1]) }}" data-redirect-after="3500">
        <div class="flex flex-col items-center gap-[20px]">
            {{-- Figma: 96px doira, 3 ta 10px nuqta (oraliq 6px). Nuqtalar bitta chiziqda to'lqin bo'lib harakatlanadi --}}
            <div class="relative flex size-[96px] items-center justify-center rounded-[48px] bg-[rgba(34,123,252,0.1)]"
                 role="status" aria-label="To‘lov tekshirilmoqda">
                <span class="loader-ring absolute inset-0 rounded-[48px]"></span>
                <span class="dot-wave flex items-center gap-[6px]">
                    <span class="size-[10px] rounded-full bg-primary"></span>
                    <span class="size-[10px] rounded-full bg-primary"></span>
                    <span class="size-[10px] rounded-full bg-primary"></span>
                </span>
            </div>
            <div class="flex flex-col items-center gap-[12px] text-center">
                <h1 class="text-[22px] leading-normal font-semibold whitespace-nowrap text-ink">To‘lov tekshirilmoqda</h1>
                <p class="w-[300px] text-[14px] leading-[20px] text-ink-3">Bank tasdiqlashini kutyapmiz. Odatda 10–30 soniya davom etadi. Ilovani yopmang.</p>
            </div>
        </div>
        <div class="flex w-full flex-col items-center gap-[4px] overflow-clip rounded-[16px] bg-[#f6f7f9] p-[16px] leading-normal whitespace-nowrap">
            <p class="text-[22px] font-bold text-ink">{{ number_format((int) ($totalRub ?? 78160), 0, '.', ' ') }} ₽</p>
            <p class="text-[12px] text-ink-3">Buyurtma VS-{{ $orderNumber ?? config('catalog.order.id') }} · {{ $cardMasked ?? 'MIR •4417' }}</p>
        </div>
    </div>
</x-layouts.app>
