@props(['product'])

{{-- Figma: Product_card/phone-* --}}
<div class="flex min-w-0 flex-1 flex-col rounded-[24px] bg-white p-[4px]">
    <a href="{{ route('product', $product['slug']) }}"
       class="relative block h-[167px] w-full overflow-clip rounded-[20px] bg-[#f8f7f5]">
        <img src="{{ asset($product['image']) }}" alt="{{ $product['title'] }}"
             class="absolute top-[calc(50%+0.5px)] left-1/2 h-[132px] w-[107px] -translate-x-1/2 -translate-y-1/2 object-cover">
    </a>
    <div class="flex flex-1 flex-col justify-between gap-[10px] overflow-clip px-[8px] pt-[10px] pb-[4px]">
        <a href="{{ route('product', $product['slug']) }}" class="flex flex-col gap-[5px]">
            <span class="text-[16px] leading-normal font-semibold whitespace-nowrap text-ink">{{ $product['price'] }}</span>
            <span class="text-[13px] leading-[16px] font-normal text-ink">{{ $product['title'] }}</span>
        </a>
        <a href="{{ route('product', $product['slug']) }}"
           class="flex w-full items-center justify-center rounded-[14px] bg-primary px-[12px] py-[10px] text-[12px] leading-normal font-medium text-white transition active:scale-[.98]">
            Sotib olish
        </a>
    </div>
</div>
