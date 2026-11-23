@props(['images', 'alt' => '', 'share' => true])

{{-- Figma: "20 · Tovar — rasm galereyasi" — to'liq ekranli rasm ko'ruvchi.
     Ochish: [data-open-viewer="{index}"] elementini bosish. $images — rasm yo'llari yoki ['src' => ..., 'alt' => ...] --}}
@php
    $items = collect($images)->map(fn ($img) => is_array($img) ? $img : ['src' => $img, 'alt' => $alt])->values();
@endphp

<div id="viewer" class="fixed inset-0 z-50 hidden bg-ink text-white" role="dialog" aria-modal="true" aria-label="Rasm galereyasi">
    <div class="relative mx-auto flex h-dvh w-full max-w-[430px] flex-col" style="padding-top: env(safe-area-inset-top)">
        <div class="mt-[8px] flex h-[40px] w-full shrink-0 items-center justify-between px-[16px]">
            <button type="button" data-viewer-close class="size-[26px]" aria-label="Yopish">
                <img src="{{ asset('img/viewer-close.svg') }}" alt="" class="size-full">
            </button>
            <p class="text-[15px] leading-normal font-medium whitespace-nowrap">
                <span data-viewer-index>1</span> / {{ $items->count() }}
            </p>
            @if ($share)
                <button type="button" data-share class="size-[22px]" aria-label="Ulashish">
                    <img src="{{ asset('img/viewer-share.svg') }}" alt="" class="size-full">
                </button>
            @else
                <span class="size-[22px]"></span>
            @endif
        </div>

        <div class="flex min-h-0 flex-1 items-center">
            <div data-viewer-track class="no-scrollbar flex h-[470px] max-h-full w-full snap-x snap-mandatory overflow-x-auto overscroll-x-contain">
                @foreach ($items as $i => $img)
                    <div class="h-full w-full shrink-0 snap-center snap-always overflow-hidden">
                        <img src="{{ asset($img['src']) }}" alt="{{ $img['alt'] }} — {{ $i + 1 }}" draggable="false" loading="lazy"
                             data-zoom class="h-full w-full cursor-zoom-in object-cover transition-transform duration-200 select-none">
                    </div>
                @endforeach
            </div>
        </div>

        <div data-viewer-thumbs class="no-scrollbar flex w-full shrink-0 gap-[8px] overflow-x-auto px-[16px] pt-[20px]"
             style="padding-bottom: max(env(safe-area-inset-bottom), 34px)">
            @foreach ($items as $i => $img)
                <button type="button" data-viewer-thumb="{{ $i }}" aria-label="{{ $i + 1 }}-rasm"
                        class="group relative size-[52px] shrink-0 rounded-[10px] opacity-60 transition-opacity data-[active=true]:opacity-100">
                    <img src="{{ asset($img['src']) }}" alt="" class="absolute inset-0 size-full rounded-[10px] object-cover">
                    <span class="absolute inset-0 rounded-[10px] border-2 border-transparent group-data-[active=true]:border-primary"></span>
                </button>
            @endforeach
        </div>
    </div>
</div>
