@props(['step', 'state'])

{{-- Buyurtma bosqichi ikonkasi (16px, rangi currentColor).
       done      — yashil doirada oq ✓ (Figma)
       current   — ko'k doirada bosqichning o'z ikonkasi (oq)
       pending   — kulrang doirada bosqichning o'z ikonkasi (kulrang)
       cancelled — qizil doirada ✕
     Bosqichlar: 1 karta (to'landi), 2 quti (tayyorlanmoqda), 3 yuk mashinasi (kuryer), 4 uy (yetkazildi) --}}
@php
    $icon = match (true) {
        $state === 'done' => 'check',
        $state === 'cancelled' => 'x',
        default => [1 => 'card', 2 => 'box', 3 => 'truck', 4 => 'home'][$step] ?? 'home',
    };
@endphp

<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
     {{ $attributes->class('size-[16px]') }} aria-hidden="true">
    @switch($icon)
        @case('check')
            <path d="M3.5 8.5l3 3 6-6.5" stroke-width="1.8"/>
            @break
        @case('x')
            <path d="M4.5 4.5l7 7M11.5 4.5l-7 7" stroke-width="1.8"/>
            @break
        @case('card')
            <rect x="2" y="3.5" width="12" height="9" rx="1.8"/>
            <path d="M2 6.5h12M4.5 10h2.5"/>
            @break
        @case('box')
            <path d="M8 1.8l5.5 2.9v6.6L8 14.2l-5.5-2.9V4.7L8 1.8z"/>
            <path d="M2.7 4.8L8 7.6l5.3-2.8M8 7.6v6.4"/>
            @break
        @case('truck')
            <path d="M1.5 4h7.5v6.5h-7.5zM9 6.5h2.8l2.2 2.3v1.7H9z"/>
            <circle cx="4.2" cy="11.6" r="1.3" fill="currentColor" stroke="none"/>
            <circle cx="11.6" cy="11.6" r="1.3" fill="currentColor" stroke="none"/>
            @break
        @default
            <path d="M2.5 7.2L8 2.8l5.5 4.4V13a.8.8 0 0 1-.8.8H10V10H6v3.8H3.3a.8.8 0 0 1-.8-.8V7.2z"/>
    @endswitch
</svg>
