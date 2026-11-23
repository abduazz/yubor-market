@props(['banners', 'interval' => 4000])

{{-- Bosh sahifa banneri (Figma: "UZ", h 120 → 126). Bir nechta banner avtomatik aylanadi,
barmoq/sichqoncha bilan surish mumkin. JS: initBannerCarousel()
Joylashuv (hamma bannerlar uchun bir xil):
matn — chapda 20px, vertikal markazda, kengligi 150px (rasm ostiga kirmaydi)
rasm — o'ng tomonda, pastki chetga tayangan (pastdan biroz chiqib turadi)
slaydlar orasida 8px bo'shliq, har biri o'z radiusi bilan --}}
<div class="relative h-[126px] w-full" data-carousel data-interval="{{ $interval }}" aria-roledescription="carousel"
    aria-label="Aksiyalar">
    <div data-carousel-track
        class="no-scrollbar flex h-full w-full snap-x snap-mandatory gap-[8px] overflow-x-auto rounded-[24px]">
        @foreach ($banners as $i => $banner)
            @php
                $isFullImage = empty($banner['title']) || ($banner['image_class'] ?? '') === 'full';
            @endphp
            <a href="{{ $banner['url'] ?? '/' }}" aria-roledescription="slide"
                aria-label="{{ $i + 1 }} / {{ count($banners) }}"
                class="relative flex h-full w-full shrink-0 snap-center snap-always items-center overflow-clip rounded-[24px] {{ $isFullImage ? 'bg-[#f6f7f9]' : 'pl-[20px]' }}"
                @if (!$isFullImage && !empty($banner['gradient'])) style="background-image: {{ $banner['gradient'] }};"
                @endif>
                @if ($isFullImage)
                    <img src="{{ asset($banner['image']) }}" alt="" draggable="false" @if ($i > 0) loading="lazy" @endif
                        class="h-full w-full object-cover select-none">
                @else
                    <div class="relative z-10 flex w-[150px] flex-col justify-center gap-[6px] pb-[6px]">
                        <p class="font-poppins text-[19px] leading-[23px] font-semibold text-white">{!! $banner['title'] !!}</p>
                        @if (!empty($banner['subtitle']))
                            <p class="font-poppins text-[10px] leading-[15px] font-medium"
                                style="color: {{ $banner['subtitle_color'] ?? '#d0e7fd' }}">{{ $banner['subtitle'] }}</p>
                        @endif
                    </div>
                    <img src="{{ asset($banner['image']) }}" alt="" draggable="false" @if ($i > 0) loading="lazy" @endif
                        class="pointer-events-none absolute max-w-none object-contain select-none {{ $banner['image_class'] ?? '' }}">
                @endif
            </a>
        @endforeach
    </div>

    @if (count($banners) > 1)
        <div class="pointer-events-none absolute bottom-[10px] left-[20px] z-20 flex items-center gap-[4px]"
            data-carousel-dots>
            @foreach ($banners as $i => $banner)
                <span
                    class="h-[4px] rounded-[2px] bg-white transition-all duration-300 {{ $i === 0 ? 'w-[14px]' : 'w-[4px] opacity-50' }}"></span>
            @endforeach
        </div>
    @endif
</div>