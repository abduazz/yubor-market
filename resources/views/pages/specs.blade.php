{{-- Figma: "18 · Tovar — barcha xususiyatlar" (2076:2481) --}}
<x-layouts.app :title="'Xususiyatlar — '.$product['full_title']">
    <div class="flex w-full flex-col pb-[24px]">
        <header class="sticky top-0 z-20 flex w-full items-center gap-[12px] bg-page px-[16px] pt-[10px] pb-[12px]">
            <a href="{{ route('product', $product['slug']) }}" class="size-[24px] shrink-0" aria-label="Orqaga">
                <img src="{{ asset('img/icon-back.svg') }}" alt="" class="size-full">
            </a>
            <h1 class="min-w-0 flex-1 text-[18px] leading-normal font-semibold text-ink">Xususiyatlar</h1>
        </header>

        <div class="flex w-full flex-col gap-[12px] px-[16px] pt-[8px]">
            @foreach ($groups as $title => $rows)
                <section class="flex w-full flex-col gap-[10px] overflow-clip rounded-[20px] bg-white p-[16px]">
                    <h2 class="text-[16px] leading-normal font-semibold whitespace-nowrap text-ink">{{ $title }}</h2>
                    @foreach ($rows as $label => $value)
                        <x-spec-row :label="$label" :value="$value" />
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.app>
