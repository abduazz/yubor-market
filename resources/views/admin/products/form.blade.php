@php
    $isEdit = $product->exists;
    $specs = old('spec_keys')
        ? array_combine(old('spec_keys', []), old('spec_values', []))
        : ($product->specs ?: config('catalog.specs'));
    $imagesText = old('images_text', $isEdit && is_array($product->images) ? implode("\n", $product->images) : '');
    $specGroupsJson = old('spec_groups_json', $product->spec_groups ? json_encode($product->spec_groups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '');
@endphp

<x-admin.layouts.app :heading="$isEdit ? __('admin.products.edit_heading', ['title' => $product->title]) : __('admin.products.create_heading')"
                     :subheading="__('admin.products.form_subheading')">
    <x-slot:actions>
        <a href="{{ route('admin.products.index') }}"
           class="rounded-[12px] bg-[#f1f5f8] px-[14px] py-[10px] text-[13px] font-medium text-ink hover:bg-[#e2e8f0]">
            {{ __('admin.products.back_to_products') }}
        </a>
        @if ($isEdit)
            <a href="{{ route('product', $product->slug) }}" target="_blank"
               class="rounded-[12px] bg-[#e8f1fe] px-[14px] py-[10px] text-[13px] font-semibold text-[#227bfc] hover:bg-[#d0e7fd]">
                {{ __('admin.products.view_on_storefront') }}
            </a>
        @endif
    </x-slot:actions>

    <form action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
          method="POST" enctype="multipart/form-data"
          class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- Left 2 columns --}}
        <div class="flex flex-col gap-[20px] xl:col-span-2">
            {{-- Basic info --}}
            <div class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.products.basic_info') }}</h2>

                <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-2">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.title_label') }}</label>
                        <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                               placeholder="Smartfon Galaxy A55 5G, 8/256 GB"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.full_title_label') }}</label>
                        <input type="text" name="full_title" value="{{ old('full_title', $product->full_title) }}"
                               placeholder="Smartfon Galaxy A55 5G, 8/256 GB, ko‘k"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-3">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.slug_label') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                               placeholder="galaxy-a55"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.category_label') }}</label>
                        <select name="category_id" required
                                class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc]">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>
                                    {{ $cat->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.brand_label') }}</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" required
                               placeholder="Samsung"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-4">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.price_label') }}</label>
                        <input type="number" name="price_rub" value="{{ old('price_rub', $product->price_rub) }}" required min="1"
                               placeholder="32990"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] font-bold outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.old_price_label') }}</label>
                        <input type="number" name="old_price_rub" value="{{ old('old_price_rub', $product->old_price_rub) }}" min="1"
                               placeholder="37990"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.stock_label') }}</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 25) }}" required min="0"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.sort_label') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 1) }}" min="0"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                    </div>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.warranty_label') }}</label>
                    <input type="text" name="warranty_text" value="{{ old('warranty_text', $product->warranty_text) }}"
                           class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.description_label') }}</label>
                    <textarea name="description" rows="4"
                              class="w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] p-[12px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            {{-- Dynamic Specs Builder --}}
            <div class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-[16px] font-bold text-ink">{{ __('admin.products.specs_title') }}</h2>
                        <p class="text-[12px] text-[#737373]">{{ __('admin.products.specs_sub') }}</p>
                    </div>
                    <button type="button" onclick="addSpecRow()"
                            class="rounded-[10px] bg-[#e8f1fe] px-[12px] py-[6px] text-[12px] font-semibold text-[#227bfc] hover:bg-[#d0e7fd]">
                        {{ __('admin.products.add_spec_row') }}
                    </button>
                </div>

                <div id="specs-container" class="flex flex-col gap-[8px]">
                    @foreach ($specs as $label => $val)
                        <div class="flex items-center gap-[8px]" data-spec-row>
                            <input type="text" name="spec_keys[]" value="{{ $label }}" placeholder="{{ __('admin.products.spec_name_placeholder') }}"
                                   class="h-[40px] w-1/3 rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            <input type="text" name="spec_values[]" value="{{ $val }}" placeholder="{{ __('admin.products.spec_val_placeholder') }}"
                                   class="h-[40px] flex-1 rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            <button type="button" onclick="this.closest('[data-spec-row]').remove()"
                                    class="flex size-[40px] shrink-0 items-center justify-center rounded-[10px] bg-[#fee4e2] text-[#d92d20] hover:bg-[#fecdca]">
                                ✕
                            </button>
                        </div>
                    @endforeach
                </div>

                <details class="mt-[6px] rounded-[12px] border border-[#eef2f6] bg-[#f8fafc] p-[12px]">
                    <summary class="cursor-pointer text-[12px] font-semibold text-[#525252]">
                        {{ __('admin.products.spec_groups_summary') }}
                    </summary>
                    <div class="mt-[10px]">
                        <textarea name="spec_groups_json" rows="8" placeholder='{"Asosiy": {"Brend": "Samsung"}}'
                                  class="w-full rounded-[10px] border border-[#dbe4ee] bg-white p-[10px] font-mono text-[12px]">{{ $specGroupsJson }}</textarea>
                    </div>
                </details>
            </div>
        </div>

        {{-- Right column: Media & Publish --}}
        <div class="flex flex-col gap-[20px]">
            <div class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.products.publish_title') }}</h2>
                <label class="flex cursor-pointer items-center justify-between rounded-[14px] bg-[#f8fafc] p-[12px]">
                    <div>
                        <p class="text-[13px] font-semibold text-ink">{{ __('admin.products.show_on_storefront') }}</p>
                        <p class="text-[11px] text-[#737373]">{{ __('admin.products.available_for_purchase') }}</p>
                    </div>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))
                           class="size-[18px] accent-[#227bfc]">
                </label>

                <button type="submit"
                        class="flex h-[48px] w-full items-center justify-center rounded-[14px] bg-[#227bfc] text-[14px] font-semibold text-white transition hover:bg-[#1a68db]">
                    {{ $isEdit ? __('admin.save_changes') : __('admin.products.create_submit') }}
                </button>
            </div>

            <div class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.products.images_title') }}</h2>

                <div class="flex items-center gap-[12px] rounded-[14px] bg-[#f8f7f5] p-[12px]">
                    <img id="preview-main-img" src="{{ asset($product->image ?: 'img/phone-blue.png') }}" alt=""
                         class="h-[72px] w-[60px] rounded-[10px] bg-white object-cover p-[2px]">
                    <div class="text-[12px] text-[#737373]">
                        <p class="font-semibold text-ink">{{ __('admin.products.card_thumb_title') }}</p>
                        <p>{{ __('admin.products.card_thumb_sub') }}</p>
                    </div>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.select_from_media') }}</label>
                    <select name="image" onchange="document.getElementById('preview-main-img').src = '/' + this.value"
                            class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                        @foreach ($availableImages as $path => $label)
                            <option value="{{ $path }}" @selected(old('image', $product->image) === $path)>{{ $label }} ({{ $path }})</option>
                        @endforeach
                        @if ($product->image && !isset($availableImages[$product->image]))
                            <option value="{{ $product->image }}" selected>{{ $product->image }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.upload_new_card_img') }}</label>
                    <input type="file" name="image_upload" accept="image/*"
                           class="w-full rounded-[12px] border border-dashed border-[#dbe4ee] bg-[#f8fafc] p-[10px] text-[12px]">
                </div>

                <hr class="border-[#eef2f6]">

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.main_gallery_img') }}</label>
                    <select name="gallery" class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                        <option value="">— {{ app()->getLocale() === 'uz' ? 'Kartochka rasmi bilan bir xil (Avto)' : 'Такое же, как фото карточки (Авто)' }} —</option>
                        @foreach ($availableImages as $path => $label)
                            <option value="{{ $path }}" @selected(old('gallery', $product->gallery) === $path)>{{ $label }} ({{ $path }})</option>
                        @endforeach
                        @if ($product->gallery && !isset($availableImages[$product->gallery]))
                            <option value="{{ $product->gallery }}" selected>{{ $product->gallery }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">
                        {{ app()->getLocale() === 'uz' ? 'Yoki galereya uchun rasmlar yuklash (bir nechta tanlash mumkin)' : 'Или загрузить фото для галереи (можно несколько)' }}
                    </label>
                    <input type="file" name="gallery_uploads[]" multiple accept="image/*"
                           class="w-full rounded-[12px] border border-dashed border-[#dbe4ee] bg-[#f8fafc] p-[10px] text-[12px]">
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.products.all_gallery_imgs') }}</label>
                    <textarea name="images_text" rows="4"
                              placeholder="{{ app()->getLocale() === 'uz' ? 'Bo‘sh qoldirilsa — yuklangan asosiy rasm ishlatiladi' : 'Если оставить пустым — используется загруженное фото' }}"
                              class="w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] p-[10px] font-mono text-[12px]">{{ $imagesText }}</textarea>
                </div>
            </div>
        </div>
    </form>

    <script>
        function addSpecRow() {
            const box = document.getElementById('specs-container');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-[8px]';
            row.dataset.specRow = '';
            row.innerHTML = `
                <input type="text" name="spec_keys[]" placeholder="{{ __('admin.products.spec_name_placeholder') }}"
                       class="h-[40px] w-1/3 rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                <input type="text" name="spec_values[]" placeholder="{{ __('admin.products.spec_val_placeholder') }}"
                       class="h-[40px] flex-1 rounded-[10px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                <button type="button" onclick="this.closest('[data-spec-row]').remove()"
                        class="flex size-[40px] shrink-0 items-center justify-center rounded-[10px] bg-[#fee4e2] text-[#d92d20] hover:bg-[#fecdca]">✕</button>
            `;
            box.appendChild(row);
        }
    </script>
</x-admin.layouts.app>
