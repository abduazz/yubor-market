@props(['title', 'text' => '', 'image' => null, 'price' => null])

{{-- Ulashish. Telefonda (navigator.share bor joyda) tizimning o'z oynasi ochiladi:
     Telegram, WhatsApp, SMS, AirDrop va h.k. Tizim oynasi bo'lmasa (masalan, kompyuter brauzeri
     yoki http orqali ochilgan sahifa) — shu bottom sheet ko'rsatiladi. JS: initShare() --}}
@php
    $apps = [
        ['key' => 'telegram', 'label' => 'Telegram', 'bg' => '#29a9eb',
         'icon' => '<path fill="#fff" d="M5.4 11.6 17.1 7c.6-.2 1 .1.9.9l-2 9.4c-.1.7-.6.8-1.1.5l-3-2.2-1.5 1.4c-.2.2-.3.3-.6.3l.2-3.1 5.6-5.1c.3-.2 0-.4-.4-.1l-6.9 4.3-3-.9c-.6-.2-.7-.7.1-1z"/>'],
        ['key' => 'whatsapp', 'label' => 'WhatsApp', 'bg' => '#25d366',
         'icon' => '<path fill="#fff" d="M12 4.5a7.4 7.4 0 0 0-6.4 11.2l-1 3.7 3.8-1A7.4 7.4 0 1 0 12 4.5zm0 13.5c-1.1 0-2.2-.3-3.1-.9l-.2-.1-2.3.6.6-2.2-.1-.2A6.1 6.1 0 1 1 12 18zm3.4-4.6c-.2-.1-1.1-.5-1.3-.6-.2-.1-.3-.1-.4.1l-.6.7c-.1.1-.2.1-.4 0a5 5 0 0 1-2.5-2.2c-.2-.3.2-.3.5-1 .1-.1 0-.2 0-.3l-.6-1.4c-.1-.4-.3-.3-.4-.3h-.4c-.1 0-.3 0-.5.2-.2.2-.7.7-.7 1.6s.7 1.9.8 2c.1.1 1.4 2.2 3.4 3 1.3.5 1.8.6 2.4.5.4-.1 1.1-.5 1.3-.9.2-.4.2-.8.1-.9l-.3-.5z"/>'],
        ['key' => 'vk', 'label' => 'VK', 'bg' => '#0077ff',
         'icon' => '<path fill="#fff" d="M12.8 16.6c-4.6 0-7.2-3.1-7.3-8.4h2.3c.1 3.9 1.8 5.5 3.1 5.8V8.2h2.2v3.3c1.3-.1 2.6-1.6 3.1-3.3h2.1a6.3 6.3 0 0 1-2.9 4.1 6.5 6.5 0 0 1 3.4 4.3h-2.4a4.1 4.1 0 0 0-3.3-3v3h-.3z"/>'],
        ['key' => 'sms', 'label' => 'SMS', 'bg' => '#34c759',
         'icon' => '<path fill="#fff" d="M12 5.5c-4.1 0-7.5 2.7-7.5 6.1 0 1.9 1.1 3.6 2.8 4.7l-.6 2.6 3-1.7c.7.2 1.5.3 2.3.3 4.1 0 7.5-2.7 7.5-6S16.1 5.5 12 5.5z"/>'],
        ['key' => 'email', 'label' => 'Email', 'bg' => '#737373',
         'icon' => '<path fill="none" stroke="#fff" stroke-width="1.6" stroke-linejoin="round" d="M5 7.5h14v9H5z M5.3 7.8 12 13l6.7-5.2"/>'],
    ];
@endphp

<div class="sheet fixed inset-0 z-[60]" id="share-sheet" data-open="false" aria-hidden="true"
     data-share-title="{{ $title }}" data-share-text="{{ $text }}">
    <div class="sheet-overlay absolute inset-0 bg-[rgba(13,20,38,0.45)]" data-close-sheet></div>

    <div role="dialog" aria-modal="true" aria-labelledby="share-sheet-title"
         class="sheet-panel absolute bottom-0 left-1/2 flex w-full max-w-[430px] flex-col gap-[16px] overflow-clip rounded-t-[24px] bg-white px-[16px] pt-[10px]"
         style="padding-bottom: max(env(safe-area-inset-bottom), 24px)">
        <div class="flex w-full justify-center">
            <span class="h-[5px] w-[36px] rounded-[3px] bg-line"></span>
        </div>

        <div class="flex w-full items-center justify-between">
            <p id="share-sheet-title" class="text-[18px] leading-normal font-semibold text-ink">Ulashish</p>
            <button type="button" class="size-[22px]" data-close-sheet aria-label="Yopish">
                <img src="{{ asset('img/icon-close.svg') }}" alt="" class="size-full">
            </button>
        </div>

        {{-- Nima ulashilayotgani --}}
        <div class="flex w-full items-center gap-[12px] rounded-[16px] bg-page p-[10px]">
            @if ($image)
                <img src="{{ asset($image) }}" alt="" class="size-[48px] shrink-0 rounded-[12px] bg-[#f8f7f5] object-cover">
            @endif
            <div class="flex min-w-0 flex-1 flex-col gap-[2px] leading-normal">
                <p class="truncate text-[14px] font-medium text-ink">{{ $title }}</p>
                @if ($price)
                    <p class="text-[13px] font-semibold text-primary">{{ $price }}</p>
                @endif
            </div>
        </div>

        {{-- Ilovalar --}}
        <div class="no-scrollbar -mx-[16px] flex gap-[4px] overflow-x-auto px-[16px]">
            @foreach ($apps as $app)
                <a href="#" data-share-app="{{ $app['key'] }}" target="_blank" rel="noopener"
                   class="flex w-[64px] shrink-0 flex-col items-center gap-[6px] transition active:scale-95">
                    <span class="flex size-[52px] items-center justify-center rounded-full" style="background: {{ $app['bg'] }}">
                        <svg viewBox="0 0 24 24" class="size-[28px]" aria-hidden="true">{!! $app['icon'] !!}</svg>
                    </span>
                    <span class="text-[12px] leading-[16px] text-ink-2">{{ $app['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Havolani nusxalash --}}
        <div class="flex w-full items-center gap-[10px] rounded-[16px] bg-field py-[8px] pr-[8px] pl-[14px]">
            <span class="min-w-0 flex-1 truncate text-[14px] leading-normal text-ink-3" data-share-url></span>
            <button type="button" data-share-copy
                    class="shrink-0 rounded-[12px] bg-primary px-[14px] py-[8px] text-[14px] leading-[20px] font-medium text-white transition active:scale-95 data-[copied=true]:bg-success">
                Nusxalash
            </button>
        </div>
    </div>
</div>
