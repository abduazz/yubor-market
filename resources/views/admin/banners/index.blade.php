<x-admin.layouts.app :heading="__('admin.banners.heading')" :subheading="__('admin.banners.subheading')">
    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        {{-- Existing Banners --}}
        <div class="flex flex-col gap-[16px] xl:col-span-2">
            @forelse ($banners as $banner)
                @php
                    $isFullImage = empty($banner->title) || $banner->image_class === 'full';
                    $localeImages = $banner->allLocaleImages();
                @endphp
                <div class="flex flex-col gap-[16px] rounded-[20px] bg-white p-[20px] shadow-2xs" data-banner-card>
                    <div class="flex flex-wrap items-center justify-between gap-[16px]">
                        {{-- Banner Preview (398x126 proportion) --}}
                        <div class="relative flex h-[126px] w-full max-w-[398px] shrink-0 items-center overflow-clip rounded-[24px] {{ $isFullImage ? 'bg-[#f6f7f9]' : 'pl-[20px]' }}"
                            @if (!$isFullImage && $banner->gradient) style="background-image: {{ $banner->gradient }};" @endif>
                            @if ($isFullImage)
                                @foreach ($locales as $locKey => $locLabel)
                                    @php $imgPath = $localeImages[$locKey] ?? null; @endphp
                                    <div class="{{ $locKey === 'uz' ? '' : 'hidden' }} h-full w-full" data-banner-preview-pane="{{ $locKey }}">
                                        @if ($imgPath)
                                            <img src="{{ asset($imgPath) }}" alt="Banner #{{ $banner->id }} ({{ $locLabel }})"
                                                 class="h-full w-full object-cover select-none">
                                        @else
                                            <div class="flex h-full w-full flex-col items-center justify-center gap-[4px] bg-[#f8fafc] text-center">
                                                <span class="rounded-[6px] bg-[#e2e8f0] px-[8px] py-[2px] text-[11px] font-bold text-[#525252]">{{ $locLabel }}</span>
                                                <span class="text-[12px] text-[#737373]">{{ __('admin.banners.no_image_for_lang') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="relative z-10 flex w-[150px] flex-col justify-center gap-[6px] pb-[6px]">
                                    <p class="font-poppins text-[19px] leading-[23px] font-semibold text-white">
                                        {!! $banner->title !!}</p>
                                    <p class="font-poppins text-[10px] leading-[15px] font-medium"
                                       style="color: {{ $banner->subtitle_color ?: '#d0e7fd' }}">
                                        {{ $banner->subtitle }}
                                    </p>
                                </div>
                                <img src="{{ asset($banner->image) }}" alt=""
                                     class="pointer-events-none absolute max-w-none object-contain select-none {{ $banner->image_class }}">
                            @endif
                        </div>

                        <div class="flex items-center gap-[8px]">
                            <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-[10px] px-[12px] py-[6px] text-[12px] font-semibold {{ $banner->is_active ? 'bg-[#d1fadf] text-[#039855]' : 'bg-[#fee4e2] text-[#d92d20]' }}">
                                    {{ $banner->is_active ? __('admin.active') : __('admin.hidden') }}
                                </button>
                            </form>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST"
                                  onsubmit="return confirm('{{ __('admin.banners.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-[10px] bg-[#fee4e2] px-[12px] py-[6px] text-[12px] font-semibold text-[#d92d20]">
                                    {{ __('admin.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- 5-Language Tabs & Upload Form --}}
                    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data"
                          class="flex flex-col gap-[12px] border-t border-[#eef2f6] pt-[14px]">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-wrap items-center justify-between gap-[8px]">
                            <span class="text-[11px] font-semibold tracking-wide text-[#737373] uppercase">{{ __('admin.banners.lang_versions_label') }}</span>
                            <div class="inline-flex flex-wrap items-center gap-[4px] rounded-[12px] bg-[#f1f5f8] p-[3px]">
                                @foreach ($locales as $locKey => $locLabel)
                                    @php $hasImg = !empty($localeImages[$locKey]); @endphp
                                    <button type="button"
                                            data-banner-tab="{{ $locKey }}"
                                            class="inline-flex items-center gap-[5px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition {{ $locKey === 'uz' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#525252] hover:text-ink' }}">
                                        <span>{{ $locLabel }}</span>
                                        <span class="size-[6px] rounded-full {{ $hasImg ? 'bg-[#12b76a]' : 'bg-[#cbd5e1]' }}"></span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col gap-[12px] sm:flex-row sm:items-end">
                            <div class="flex-1">
                                @foreach ($locales as $locKey => $locLabel)
                                    <div class="{{ $locKey === 'uz' ? '' : 'hidden' }}" data-banner-upload-pane="{{ $locKey }}">
                                        <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">
                                            {{ __('admin.banners.replace_photo_lang', ['lang' => $locLabel]) }}
                                        </label>
                                        <input type="file" name="images_upload[{{ $locKey }}]" accept=".png,.jpg,.jpeg,.webp,image/*"
                                               class="block h-[38px] w-full cursor-pointer rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] py-[6px] text-[12px] text-[#525252] file:mr-[10px] file:cursor-pointer file:rounded-[6px] file:border-0 file:bg-[#e8f1fe] file:px-[10px] file:py-[3px] file:text-[11px] file:font-semibold file:text-[#227bfc]">
                                    </div>
                                @endforeach
                            </div>

                            <div class="sm:w-[220px]">
                                <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.banners.url_label') }}</label>
                                <input type="text" name="url" value="{{ $banner->url }}" placeholder="/category/telefonlar"
                                       class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                            </div>
                            @if ($banner->is_active)
                                <input type="hidden" name="is_active" value="1">
                            @endif
                            <button type="submit"
                                    class="h-[38px] shrink-0 rounded-[10px] bg-[#227bfc] px-[16px] text-[12px] font-semibold text-white hover:bg-[#1a68db]">
                                {{ __('admin.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="rounded-[20px] bg-white p-[32px] text-center text-[14px] text-[#737373] shadow-2xs">
                    {{ __('admin.banners.empty') }}
                </div>
            @endforelse
        </div>

        {{-- Upload New Banner (5 Languages) --}}
        <div class="h-fit rounded-[20px] bg-white p-[20px] shadow-2xs" data-banner-card>
            <h2 class="mb-[6px] text-[16px] font-bold text-ink">{{ __('admin.banners.add_heading') }}</h2>
            <p class="mb-[14px] text-[12px] leading-[17px] text-[#737373]">{{ __('admin.banners.format_hint') }}</p>

            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data"
                  class="flex flex-col gap-[14px]">
                @csrf

                <div class="flex flex-wrap items-center gap-[4px] rounded-[12px] bg-[#f1f5f8] p-[3px]">
                    @foreach ($locales as $locKey => $locLabel)
                        <button type="button"
                                data-new-banner-tab="{{ $locKey }}"
                                class="inline-flex flex-1 items-center justify-center gap-[5px] rounded-[9px] px-[8px] py-[6px] text-[12px] font-semibold transition {{ $locKey === 'uz' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#525252] hover:text-ink' }}">
                            <span>{{ $locLabel }}</span>
                            <span data-new-banner-dot="{{ $locKey }}" class="size-[6px] rounded-full bg-[#cbd5e1]"></span>
                        </button>
                    @endforeach
                </div>

                @foreach ($locales as $locKey => $locLabel)
                    <div class="{{ $locKey === 'uz' ? '' : 'hidden' }}" data-new-banner-pane="{{ $locKey }}">
                        <label class="mb-[6px] block text-[12px] font-medium text-[#525252]">
                            {{ __('admin.banners.upload_lang_label', ['lang' => $locLabel]) }}
                        </label>
                        <label class="flex cursor-pointer flex-col items-center justify-center gap-[8px] rounded-[16px] border-2 border-dashed border-[#c8d3df] bg-[#f8fafc] p-[20px] text-center transition hover:border-[#227bfc] hover:bg-[#f0f7ff]">
                            <div id="new-banner-preview-wrap-{{ $locKey }}" class="hidden w-full overflow-clip rounded-[18px]">
                                <img id="new-banner-preview-{{ $locKey }}" src="" alt="" class="h-[110px] w-full object-cover">
                            </div>
                            <span class="text-[13px] font-semibold text-[#227bfc]">{{ __('admin.banners.choose_file') }} ({{ $locLabel }})</span>
                            <span class="text-[11px] text-[#737373]">796 × 252 px (398 × 126 px) · PNG, JPG, WebP</span>
                            <input type="file" name="images_upload[{{ $locKey }}]" accept=".png,.jpg,.jpeg,.webp,image/*"
                                   class="sr-only"
                                   onchange="const f=this.files[0];if(f){const img=document.getElementById('new-banner-preview-{{ $locKey }}');img.src=URL.createObjectURL(f);document.getElementById('new-banner-preview-wrap-{{ $locKey }}').classList.remove('hidden');const dot=document.querySelector('[data-new-banner-dot={{ $locKey }}]');if(dot){dot.classList.remove('bg-[#cbd5e1]');dot.classList.add('bg-[#12b76a]');}}">
                        </label>
                    </div>
                @endforeach

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.banners.url_optional_label') }}</label>
                    <input type="text" name="url" value="/" placeholder="/category/telefonlar"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>

                <input type="hidden" name="is_active" value="1">
                <button type="submit"
                        class="mt-[2px] h-[44px] rounded-[12px] bg-[#227bfc] text-[13px] font-semibold text-white hover:bg-[#1a68db]">
                    {{ __('admin.banners.add_to_carousel') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-banner-card]').forEach((card) => {
                const tabs = card.querySelectorAll('[data-banner-tab]');
                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        const loc = tab.dataset.bannerTab;
                        tabs.forEach((t) => {
                            const active = t.dataset.bannerTab === loc;
                            t.className = 'inline-flex items-center gap-[5px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition ' +
                                (active ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#525252] hover:text-ink');
                        });
                        card.querySelectorAll('[data-banner-preview-pane]').forEach((p) => {
                            p.classList.toggle('hidden', p.dataset.bannerPreviewPane !== loc);
                        });
                        card.querySelectorAll('[data-banner-upload-pane]').forEach((p) => {
                            p.classList.toggle('hidden', p.dataset.bannerUploadPane !== loc);
                        });
                    });
                });
            });

            const newTabs = document.querySelectorAll('[data-new-banner-tab]');
            newTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const loc = tab.dataset.newBannerTab;
                    newTabs.forEach((t) => {
                        const active = t.dataset.newBannerTab === loc;
                        t.className = 'inline-flex flex-1 items-center justify-center gap-[5px] rounded-[9px] px-[8px] py-[6px] text-[12px] font-semibold transition ' +
                            (active ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#525252] hover:text-ink');
                    });
                    document.querySelectorAll('[data-new-banner-pane]').forEach((p) => {
                        p.classList.toggle('hidden', p.dataset.newBannerPane !== loc);
                    });
                });
            });
        })();
    </script>
</x-admin.layouts.app>
