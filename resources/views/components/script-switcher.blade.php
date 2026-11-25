@props(['variant' => 'light'])
@php
    $script = $storefrontScript ?? 'lat';
@endphp

<div data-no-transliterate
     class="inline-flex shrink-0 items-center rounded-[10px] p-[2px] {{ $variant === 'glass' ? 'bg-[rgba(255,255,255,0.92)] shadow-xs' : 'bg-[#f1f5f8]' }}"
     role="group" aria-label="Alifbo">
    <a href="{{ route('storefront.script', 'lat') }}"
       class="rounded-[8px] px-[7px] py-[4px] text-[11px] leading-none font-semibold transition {{ $script === 'lat' ? 'bg-primary text-white' : 'text-ink-3 hover:text-ink' }}">
        O‘z
    </a>
    <a href="{{ route('storefront.script', 'cyrl') }}"
       class="rounded-[8px] px-[7px] py-[4px] text-[11px] leading-none font-semibold transition {{ $script === 'cyrl' ? 'bg-primary text-white' : 'text-ink-3 hover:text-ink' }}">
        Ўз
    </a>
</div>
