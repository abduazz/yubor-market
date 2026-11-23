@php
    $list = array_merge($products, $products);
@endphp

<x-layouts.app :title="$category['title'].' — Yubor Market'">
    <div class="flex min-h-dvh w-full flex-col gap-[8px]">
        <x-nav :title="$category['title']" :back="route('home')" />

        {{-- Oq karta ekranning pastigacha cho'ziladi (pastda kulrang bo'shliq qolmaydi) --}}
        <div class="flex w-full flex-1 flex-col rounded-t-[16px] bg-white px-[12px] pt-[12px]"
             style="padding-bottom: max(env(safe-area-inset-bottom), 24px)">
            @if (count($list))
                <div class="flex w-full flex-col gap-[10px]">
                    @foreach (collect($list)->chunk(2) as $row)
                        <div class="flex w-full items-stretch">
                            @foreach ($row as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-[40px] text-center text-[14px] text-ink-3">Bu bo‘limda hozircha mahsulot yo‘q</p>
            @endif
        </div>
    </div>
</x-layouts.app>
