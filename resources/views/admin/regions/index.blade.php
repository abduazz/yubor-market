<x-admin.layouts.app :heading="__('admin.regions.heading')" :subheading="__('admin.regions.subheading')">
    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        <div class="flex flex-col gap-[14px] xl:col-span-2">
            @foreach ($regions as $region)
                <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
                    <div class="flex flex-wrap items-center justify-between gap-[12px] border-b border-[#eef2f6] pb-[12px]">
                        <div class="flex items-center gap-[10px]">
                            <img src="{{ asset('img/icon-map-point.svg') }}" alt="" class="size-[22px]">
                            <div>
                                <h3 class="text-[16px] font-bold text-ink">{{ $region->name }}</h3>
                                <p class="text-[12px] text-[#737373]">{{ __('admin.regions.delivery_time', ['days' => $region->delivery_days, 'count' => $region->districts->count()]) }}</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.regions.districts.store', $region) }}" method="POST" class="flex items-center gap-[8px]">
                            @csrf
                            <input type="text" name="name" required placeholder="{{ __('admin.regions.add_district_placeholder') }}"
                                   class="h-[36px] rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[12px]">
                            <button type="submit" class="h-[36px] rounded-[10px] bg-[#e8f1fe] px-[12px] text-[12px] font-semibold text-[#227bfc] hover:bg-[#d0e7fd]">
                                {{ __('admin.regions.add_district_btn') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-[12px] flex flex-wrap gap-[8px]">
                        @foreach ($region->districts as $district)
                            <span class="inline-flex items-center gap-[6px] rounded-[10px] bg-[#f1f5f8] px-[10px] py-[5px] text-[12px] font-medium text-ink">
                                <span>{{ $district->name }}</span>
                                <form action="{{ route('admin.districts.destroy', $district) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="{{ __('admin.delete') }}" class="text-[#a3a3a3] hover:text-[#d92d20]">✕</button>
                                </form>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add New Region --}}
        <div class="h-fit rounded-[20px] bg-white p-[20px] shadow-2xs">
            <h2 class="mb-[14px] text-[16px] font-bold text-ink">{{ __('admin.regions.add_region_heading') }}</h2>
            <form action="{{ route('admin.regions.store') }}" method="POST" class="flex flex-col gap-[12px]">
                @csrf
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.regions.region_name_label') }}</label>
                    <input type="text" name="name" required placeholder="Masalan: Yangi viloyat"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.regions.delivery_days_label') }}</label>
                    <input type="text" name="delivery_days" value="2–3 kun" required
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <button type="submit" class="mt-[4px] h-[44px] rounded-[12px] bg-[#227bfc] text-[13px] font-semibold text-white hover:bg-[#1a68db]">
                    {{ __('admin.regions.add_region_btn') }}
                </button>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
