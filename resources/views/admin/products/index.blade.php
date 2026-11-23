<x-admin.layouts.app :heading="__('admin.products.heading')" :subheading="__('admin.products.subheading')">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}"
           class="rounded-[12px] bg-[#227bfc] px-[16px] py-[10px] text-[13px] font-semibold text-white transition hover:bg-[#1a68db]">
            {{ __('admin.products.add_btn') }}
        </a>
    </x-slot:actions>

    {{-- Filters --}}
    <form action="{{ route('admin.products.index') }}" method="GET"
          class="flex flex-wrap items-center gap-[12px] rounded-[20px] bg-white p-[16px] shadow-2xs">
        <div class="min-w-[240px] flex-1">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('admin.products.search_placeholder') }}"
                   class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] text-ink outline-none focus:border-[#227bfc] focus:bg-white">
        </div>
        <select name="category_id" onchange="this.form.submit()"
                class="h-[42px] rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] text-ink outline-none focus:border-[#227bfc]">
            <option value="">{{ __('admin.products.all_categories') }}</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->title }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
                class="h-[42px] rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] text-ink outline-none focus:border-[#227bfc]">
            <option value="">{{ __('admin.products.all_statuses') }}</option>
            <option value="active" @selected(request('status') === 'active')>{{ __('admin.products.status_active') }}</option>
            <option value="hidden" @selected(request('status') === 'hidden')>{{ __('admin.products.status_hidden') }}</option>
        </select>
        <button type="submit" class="h-[42px] rounded-[12px] bg-[#1c1917] px-[16px] text-[13px] font-semibold text-white hover:bg-[#333]">
            {{ __('admin.filter_btn') }}
        </button>
        @if (request()->anyFilled(['q', 'category_id', 'status']))
            <a href="{{ route('admin.products.index') }}" class="text-[13px] font-medium text-[#737373] hover:text-ink">{{ __('admin.reset') }}</a>
        @endif
    </form>

    {{-- Products Table --}}
    <div class="overflow-hidden rounded-[20px] bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-[#eef2f6] bg-[#f8fafc] text-[11px] font-semibold tracking-wider text-[#737373] uppercase">
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_product') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_category') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_brand') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_price') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_stock') }}</th>
                        <th class="px-[16px] py-[12px]">{{ __('admin.products.col_storefront') }}</th>
                        <th class="px-[16px] py-[12px] text-right">{{ __('admin.products.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eef2f6]">
                    @forelse ($products as $product)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-[16px] py-[14px]">
                                <div class="flex items-center gap-[12px]">
                                    <img src="{{ asset($product->image) }}" alt=""
                                         class="h-[52px] w-[44px] shrink-0 rounded-[10px] bg-[#f8f7f5] object-cover p-[2px]">
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-ink hover:text-[#227bfc]">
                                            {{ $product->title }}
                                        </a>
                                        <p class="truncate text-[11px] text-[#737373]">/product/{{ $product->slug }} · {{ __('admin.products.photos_count', ['count' => count($product->images ?? [])]) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <span class="rounded-[8px] bg-[#f1f5f8] px-[10px] py-[4px] text-[12px] font-medium text-ink">
                                    {{ $product->category?->title ?? __('admin.products.no_category') }}
                                </span>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap font-medium text-[#227bfc]">
                                {{ $product->brand }}
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <p class="text-[14px] font-bold text-ink">{{ $product->formatted_price }}</p>
                                @if ($product->old_price_rub)
                                    <p class="text-[11px] text-[#a3a3a3] line-through">{{ $product->formatted_old_price }}</p>
                                @endif
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <span class="rounded-[8px] px-[8px] py-[3px] text-[12px] font-semibold {{ $product->stock > 10 ? 'bg-[#d1fadf] text-[#039855]' : 'bg-[#fef0c7] text-[#b54708]' }}">
                                    {{ $product->stock }} {{ __('admin.pcs') }}
                                </span>
                            </td>
                            <td class="px-[16px] py-[14px] whitespace-nowrap">
                                <form action="{{ route('admin.products.toggle', $product) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-[8px] px-[10px] py-[4px] text-[11px] font-semibold transition {{ $product->is_active ? 'bg-[#d1fadf] text-[#039855]' : 'bg-[#fee4e2] text-[#d92d20]' }}">
                                        {{ $product->is_active ? __('admin.active') : __('admin.hidden') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-[16px] py-[14px] text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-[6px]">
                                    <a href="{{ route('product', $product->slug) }}" target="_blank" title="{{ __('admin.open_storefront') }}"
                                       class="rounded-[8px] bg-[#e8f1fe] px-[10px] py-[6px] text-[12px] font-semibold text-[#227bfc] hover:bg-[#d0e7fd]">
                                        ↗
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="rounded-[8px] bg-[#f1f5f8] px-[12px] py-[6px] text-[12px] font-semibold text-ink hover:bg-[#e2e8f0]">
                                        {{ __('admin.edit') }}
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          onsubmit="return confirm('{{ __('admin.products.confirm_delete', ['title' => $product->title]) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-[8px] bg-[#fee4e2] px-[10px] py-[6px] text-[12px] font-semibold text-[#d92d20] hover:bg-[#fecdca]">
                                            {{ __('admin.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-[40px] text-center text-[14px] text-[#737373]">
                                {{ __('admin.products.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
