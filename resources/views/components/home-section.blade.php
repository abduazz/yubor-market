@props(['category', 'products', 'perPage' => 4])

{{-- Bosh sahifadagi bo'lim. Avval $perPage ta tovar ko'rinadi, "Ko‘proq ko‘rish" har bosilganda
     yana $perPage tasi ochiladi. Hammasi ochilgach (yoki tovar $perPage tadan oshmasa) tugma chiqmaydi. --}}
<section class="flex w-full flex-col gap-[16px] rounded-[16px] bg-white p-[16px]" data-load-more-section>
    <div class="flex h-[32px] w-full items-center justify-between">
        <h2 class="text-[20px] leading-[24px] font-semibold whitespace-nowrap text-ink">{{ $category['section'] }}</h2>
        <a href="{{ route('category', $category['slug']) }}"
           class="flex h-[30px] items-center justify-center gap-[2px] rounded-[12px] bg-[rgba(67,133,245,0.08)] py-[8px] pr-[6px] pl-[12px]">
            <span class="text-[14px] leading-normal font-medium tracking-[-0.1px] text-[#397beb]">Barchasi</span>
            <img src="{{ asset('img/chevron-right-blue.svg') }}" alt="" class="size-[16px]">
        </a>
    </div>

    @if (count($products))
        <div class="grid w-full grid-cols-2 gap-x-[8px] gap-y-[10px]">
            @foreach ($products as $i => $product)
                <div class="flex min-w-0 {{ $i >= $perPage ? 'hidden' : '' }}" @if ($i >= $perPage) data-more-item @endif>
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    @endif

    @if (count($products) > $perPage)
        <button type="button" data-load-more="{{ $perPage }}"
                class="flex h-[46px] w-full items-center justify-center rounded-[14px] bg-[#f6f6f6] px-[12px] py-[14px] text-[14px] leading-normal font-medium text-ink transition active:scale-[.99]">
            Ko‘proq ko‘rish
        </button>
    @endif
</section>
