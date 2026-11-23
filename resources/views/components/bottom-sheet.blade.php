@props(['id', 'title', 'items', 'icon' => false, 'searchVariant' => 'region'])

{{-- Figma: "Viloyat tanlash" / "Tuman tanlash" bottom sheet --}}
<div class="sheet fixed inset-0 z-50" id="{{ $id }}" data-open="false" aria-hidden="true">
    <div class="sheet-overlay absolute inset-0 bg-[rgba(13,20,38,0.45)]" data-close-sheet></div>

    <div role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title"
         class="sheet-panel absolute bottom-0 left-1/2 flex max-h-[88dvh] w-full max-w-[430px] flex-col {{ $icon ? 'gap-[12px]' : 'gap-[14px]' }} overflow-clip rounded-t-[24px] bg-white px-[16px] pt-[10px]"
         style="padding-bottom: max(env(safe-area-inset-bottom), 34px)">
        <div class="flex w-full justify-center">
            <span class="h-[5px] w-[36px] rounded-[3px] bg-line"></span>
        </div>

        <div class="flex w-full items-center justify-between">
            <p id="{{ $id }}-title" class="text-[18px] leading-normal font-semibold whitespace-nowrap text-ink" data-sheet-title>{{ $title }}</p>
            <button type="button" class="size-[22px]" data-close-sheet aria-label="Yopish">
                <img src="{{ asset('img/icon-close.svg') }}" alt="" class="size-full">
            </button>
        </div>

        @if ($searchVariant === 'region')
            <label class="flex w-full items-center gap-[10px] rounded-[16px] bg-[#f6f6f6] px-[14px] py-[12px]">
                <img src="{{ asset('img/icon-search.svg') }}" alt="" class="size-[20px]">
                <input type="search" placeholder="Qidirish" data-sheet-search
                       class="min-w-0 flex-1 bg-transparent text-[16px] leading-[24px] outline-none placeholder:text-[#71717a]">
            </label>
        @else
            <label class="flex w-full items-center gap-[10px] rounded-[14px] bg-page px-[14px] py-[12px]">
                <img src="{{ asset('img/icon-search-sm.svg') }}" alt="" class="size-[18px]">
                <input type="search" placeholder="Qidirish" data-sheet-search
                       class="min-w-0 flex-1 bg-transparent text-[14px] leading-normal outline-none placeholder:text-muted">
            </label>
        @endif

        <ul class="no-scrollbar -mb-px flex w-full flex-col overflow-y-auto" data-sheet-list>
            @foreach ($items as $item)
                <li>
                    <button type="button" data-sheet-option="{{ $item }}"
                            class="group flex w-full items-center justify-between border-b border-divider {{ $icon ? 'py-[12px]' : 'py-[13px]' }} text-left">
                        <span class="flex items-center gap-[10px]">
                            @if ($icon)
                                <img src="{{ asset('img/icon-map-point.svg') }}" alt="" class="size-[24px]">
                            @endif
                            <span class="text-[15px] leading-normal whitespace-nowrap text-ink group-data-[selected=true]:font-semibold">{{ $item }}</span>
                        </span>
                        <img src="{{ asset('img/icon-check.svg') }}" alt="" class="hidden size-[22px] group-data-[selected=true]:block">
                    </button>
                </li>
            @endforeach
        </ul>

        <button type="button" data-sheet-confirm
                class="flex w-full shrink-0 items-center justify-center rounded-[14px] bg-[#4385f5] px-[20px] py-[16px] text-[16px] leading-[24px] font-medium text-white disabled:opacity-50">
            Tanlash
        </button>
    </div>
</div>
