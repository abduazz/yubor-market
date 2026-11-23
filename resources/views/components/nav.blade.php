@props(['title', 'back' => url()->previous(), 'steps' => null])

{{-- Oq header: orqaga tugmasi + sarlavha (+ ixtiyoriy 3 qadamli progress) --}}
<header {{ $attributes->class('sticky top-0 z-20 w-full rounded-b-[16px] bg-white') }}>
    <div class="flex w-full items-center gap-[12px] px-[16px] pt-[10px] pb-[12px]">
        <a href="{{ $back }}" class="size-[24px] shrink-0" aria-label="Orqaga">
            <img src="{{ asset('img/icon-back.svg') }}" alt="" class="size-full">
        </a>
        <h1 class="min-w-0 flex-1 text-[18px] leading-normal font-semibold text-ink">{{ $title }}</h1>
    </div>

    @if ($steps)
        <div class="flex w-full gap-[6px] px-[16px] pb-[12px]">
            @for ($i = 1; $i <= 3; $i++)
                <div class="h-[4px] flex-1 rounded-[2px] {{ $i <= $steps ? 'bg-primary' : 'bg-line' }}"></div>
            @endfor
        </div>
    @endif
</header>
