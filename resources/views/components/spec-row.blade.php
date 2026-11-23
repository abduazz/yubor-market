@props(['label', 'value'])

{{-- Figma: Spec/* — nom ........ qiymat --}}
<div class="flex w-full items-end gap-[8px] text-[13px] leading-normal">
    <span class="shrink-0 whitespace-nowrap text-ink-3">{{ $label }}</span>
    <span class="mb-[3px] min-w-px flex-1 border-b border-dashed border-[#d4d4d4]"></span>
    <span class="shrink-0 font-medium whitespace-nowrap text-ink">{{ $value }}</span>
</div>
