<x-admin.layouts.app :heading="__('admin.categories.heading')" :subheading="__('admin.categories.subheading')">
    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        {{-- List of Categories --}}
        <div class="flex flex-col gap-[14px] xl:col-span-2">
            @foreach ($categories as $category)
                <form action="{{ route('admin.categories.update', $category) }}" method="POST"
                      class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-wrap items-center justify-between gap-[12px]">
                        <div class="flex items-center gap-[12px]">
                            @if ($category->image)
                                <img src="{{ asset($category->image) }}" alt="" class="size-[44px] rounded-[12px] bg-[#f6f6f6] object-cover p-[4px]">
                            @endif
                            <div>
                                <h3 class="text-[16px] font-bold text-ink">{{ $category->title }}</h3>
                                <p class="text-[12px] text-[#737373]">/category/{{ $category->slug }} · {{ __('admin.categories.products_count') }} <strong>{{ $category->products_count }}</strong></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-[10px]">
                            <label class="flex cursor-pointer items-center gap-[6px] text-[12px] font-medium text-[#525252]">
                                <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="size-[16px] accent-[#227bfc]">
                                <span>{{ __('admin.categories.active_label') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-5">
                        <div>
                            <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.categories.name_label') }}</label>
                            <input type="text" name="title" value="{{ $category->title }}" required
                                   class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                        </div>
                        <div>
                            <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.categories.section_label') }}</label>
                            <input type="text" name="section" value="{{ $category->section }}" required
                                   class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                        </div>
                        <div>
                            <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.categories.slug_label') }}</label>
                            <input type="text" name="slug" value="{{ $category->slug }}" required
                                   class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                        </div>
                        <div>
                            <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.categories.demo_count_label') }}</label>
                            <input type="number" name="demo_count" value="{{ $category->demo_count }}" min="1" max="40" required
                                   class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                        </div>
                        <div>
                            <label class="mb-[4px] block text-[11px] font-medium text-[#737373]">{{ __('admin.categories.sort_label') }}</label>
                            <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0"
                                   class="h-[38px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] text-[13px]">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-[12px]">
                        <div class="flex-1">
                            <input type="text" name="image" value="{{ $category->image }}" placeholder="img/cat-phones.png"
                                   class="h-[36px] w-full rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[10px] font-mono text-[12px]">
                        </div>
                        <div class="flex items-center gap-[8px]">
                            <button type="submit" class="rounded-[10px] bg-[#227bfc] px-[14px] py-[8px] text-[12px] font-semibold text-white hover:bg-[#1a68db]">
                                {{ __('admin.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>

        {{-- Create New Category --}}
        <div class="h-fit rounded-[20px] bg-white p-[20px] shadow-2xs">
            <h2 class="mb-[14px] text-[16px] font-bold text-ink">{{ __('admin.categories.add_heading') }}</h2>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="flex flex-col gap-[12px]">
                @csrf
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.new_name_label') }}</label>
                    <input type="text" name="title" required placeholder="Sovg‘a to‘plamlari"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.new_section_label') }}</label>
                    <input type="text" name="section" placeholder="Sovg‘a to‘plamlari"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.slug_label') }}</label>
                    <input type="text" name="slug" placeholder="sovga-toplamlari"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.icon_label') }}</label>
                    <input type="text" name="image" value="img/teddy.png"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                </div>
                <div class="grid grid-cols-2 gap-[10px]">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.cards_count_label') }}</label>
                        <input type="number" name="demo_count" value="4" min="1" max="40"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.categories.sort_label') }}</label>
                        <input type="number" name="sort_order" value="{{ $categories->count() + 1 }}" min="0"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>
                <label class="flex cursor-pointer items-center gap-[8px] text-[13px] text-[#525252]">
                    <input type="checkbox" name="is_active" value="1" checked class="size-[16px] accent-[#227bfc]">
                    <span>{{ __('admin.categories.show_immediately') }}</span>
                </label>
                <button type="submit" class="mt-[4px] h-[44px] rounded-[12px] bg-[#227bfc] text-[13px] font-semibold text-white hover:bg-[#1a68db]">
                    {{ __('admin.categories.create_btn') }}
                </button>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
