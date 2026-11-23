@props(['icon', 'filled' => false])

<div class="flex w-full items-center gap-[10px]">
    <div class="relative size-[40px] shrink-0 overflow-clip rounded-[12px] {{ $filled ? 'bg-primary' : 'bg-[rgba(34,123,252,0.1)]' }}">
        <img src="{{ asset($icon) }}" alt="" class="absolute top-[10px] left-[10px] size-[20px]">
    </div>
    <p class="text-[16px] leading-normal font-semibold whitespace-nowrap text-ink">{{ $slot }}</p>
</div>
