@props(['label', 'name', 'placeholder', 'sheet', 'required' => false, 'message' => null])

{{-- Viloyat / Tuman tanlash maydoni — bosilganda bottom sheet ochiladi. Holatlari field.blade.php bilan bir xil. --}}
<div class="group/field flex w-full flex-col gap-[6px]" data-field data-invalid="false">
    <button type="button" data-open-sheet="{{ $sheet }}" aria-describedby="{{ $name }}-error"
            {{ $attributes->class('group flex h-[58px] w-full items-center gap-[10px] overflow-clip rounded-[16px] border border-transparent bg-field px-[14px] py-[8px] text-left transition-colors focus-visible:border-primary focus-visible:outline-none group-data-[invalid=true]/field:!border-[#f04438] group-data-[invalid=true]/field:bg-[#fef3f2]') }}>
        <span class="flex min-w-0 flex-1 flex-col justify-center gap-[2px]">
            <span class="text-[12px] leading-[16px] text-ink-2 group-data-[filled=true]:font-medium group-data-[filled=true]:text-ink-3 group-data-[invalid=true]/field:!text-[#d92d20]">
                {{ $label }}
            </span>
            <span data-value-for="{{ $name }}" class="truncate text-[16px] leading-[24px] text-muted group-data-[filled=true]:font-medium group-data-[filled=true]:text-[#444]">{{ $placeholder }}</span>
        </span>
        <img src="{{ asset('img/icon-chev-down.svg') }}" alt="" class="size-[20px] shrink-0">
        <input type="hidden" name="{{ $name }}" @if ($required) data-required @endif
               @if ($message) data-message="{{ $message }}" @endif>
    </button>
    <p id="{{ $name }}-error" data-error role="alert"
       class="hidden items-center gap-[4px] px-[4px] text-[12px] leading-[16px] text-[#d92d20] group-data-[invalid=true]/field:flex"></p>
</div>
