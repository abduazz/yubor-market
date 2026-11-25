@php
    $def = $statusDefinitions[$order->status] ?? $statusDefinitions['shipping'];
    $palette = $statusColors[$def['color']] ?? ['bg' => '#eef2f6', 'text' => '#475467', 'accent' => '#227bfc'];
@endphp

<x-admin.layouts.app :heading="__('admin.orders.show_heading', ['number' => $order->order_number])"
                     :subheading="__('admin.orders.show_subheading', ['date' => $order->created_at?->format('d.m.Y H:i'), 'region' => $order->region, 'district' => $order->district])">
    <x-slot:actions>
        <a href="{{ route('admin.orders.index') }}"
           class="rounded-[12px] bg-[#f1f5f8] px-[14px] py-[10px] text-[13px] font-medium text-ink hover:bg-[#e2e8f0]">
            {{ __('admin.orders.back_to_orders') }}
        </a>
        <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
              onsubmit="return confirm('{{ __('admin.orders.confirm_delete', ['number' => $order->order_number]) }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-[12px] bg-[#fee4e2] px-[14px] py-[10px] text-[13px] font-semibold text-[#d92d20] hover:bg-[#fecdca]">
                {{ __('admin.delete') }}
            </button>
        </form>
    </x-slot:actions>

    {{-- Fast Status Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-[16px] rounded-[20px] bg-white p-[20px] shadow-2xs">
        <div class="flex items-center gap-[12px]">
            <span class="rounded-[10px] px-[12px] py-[6px] text-[13px] font-bold"
                  style="background: {{ $palette['bg'] }}; color: {{ $palette['text'] }}">
                {{ $def['badge'] }}
            </span>
            <div>
                <p class="text-[15px] font-bold text-ink">{{ $def['title'] }}</p>
                <p class="text-[12px] text-[#737373]">{{ __('admin.orders.step_hint', ['step' => $def['step']]) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-[6px]">
            @foreach ($statusDefinitions as $sKey => $sDef)
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $sKey }}">
                    <button type="submit"
                            class="rounded-[10px] px-[12px] py-[8px] text-[12px] font-semibold transition {{ $order->status === $sKey ? 'bg-[#227bfc] text-white shadow-xs' : 'bg-[#f1f5f8] text-[#525252] hover:bg-[#e2e8f0]' }}">
                        {{ $sDef['badge'] }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        {{-- Left 2 cols: Edit Form --}}
        <form action="{{ route('admin.orders.update', $order) }}" method="POST"
              class="flex flex-col gap-[20px] xl:col-span-2">
            @csrf
            @method('PUT')

            {{-- Sender & Recipient --}}
            <div class="grid grid-cols-1 gap-[20px] md:grid-cols-2">
                <div class="flex flex-col gap-[12px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                    <div class="flex items-center gap-[10px]">
                        <div class="flex size-[36px] items-center justify-center rounded-[10px] bg-[#e8f1fe]">
                            <img src="{{ asset('img/icon-user.svg') }}" alt="" class="size-[18px]">
                        </div>
                        <div>
                            <h2 class="text-[15px] font-bold text-ink">{{ __('admin.orders.sender_card_title') }}</h2>
                            <p class="text-[11px] text-[#737373]">{{ __('admin.orders.sender_card_sub') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.sender_name') }}</label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', $order->sender_name) }}" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.sender_phone') }}</label>
                        <input type="text" name="sender_phone" value="{{ old('sender_phone', $order->sender_phone) }}" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div class="flex flex-col gap-[12px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                    <div class="flex items-center gap-[10px]">
                        <div class="flex size-[36px] items-center justify-center rounded-[10px] bg-[#e8f1fe]">
                            <img src="{{ asset('img/icon-gift.svg') }}" alt="" class="size-[18px]">
                        </div>
                        <div>
                            <h2 class="text-[15px] font-bold text-ink">{{ __('admin.orders.recipient_card_title') }}</h2>
                            <p class="text-[11px] text-[#737373]">{{ __('admin.orders.recipient_card_sub') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.recipient_name') }}</label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div class="grid grid-cols-1 gap-[10px] sm:grid-cols-2">
                        <div>
                            <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.main_phone_uz') }}</label>
                            <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}" required
                                   class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                        </div>
                        <div>
                            <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.extra_phone_uz') }}</label>
                            <input type="text" name="recipient_phone_2" value="{{ old('recipient_phone_2', $order->recipient_phone_2) }}"
                                   class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery Address & Courier --}}
            <div class="flex flex-col gap-[14px] rounded-[20px] bg-white p-[20px] shadow-2xs">
                <div class="flex items-center gap-[10px]">
                    <div class="flex size-[36px] items-center justify-center rounded-[10px] bg-[#e8f1fe]">
                        <img src="{{ asset('img/icon-pin-blue.svg') }}" alt="" class="size-[18px]">
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-ink">{{ __('admin.orders.delivery_section_title') }}</h2>
                        <p class="text-[11px] text-[#737373]">{{ __('admin.orders.delivery_section_sub') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-3">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.status_select_label') }}</label>
                        <select name="status" class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px] font-semibold">
                            @foreach ($statusDefinitions as $sKey => $sDef)
                                <option value="{{ $sKey }}" @selected($order->status === $sKey)>{{ $sDef['badge'] }} — {{ $sDef['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.region_label') }}</label>
                        <select name="region" class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                            @foreach ($regions as $r)
                                <option value="{{ $r }}" @selected($order->region === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.district_label') }}</label>
                        <input type="text" name="district" value="{{ old('district', $order->district) }}" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-2">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.address_label') }}</label>
                        <input type="text" name="address" value="{{ old('address', $order->address) }}" required
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.landmark_label') }}</label>
                        <input type="text" name="landmark" value="{{ old('landmark', $order->landmark) }}"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-[12px] sm:grid-cols-3">
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.courier_name') }}</label>
                        <input type="text" name="courier_name" value="{{ old('courier_name', $order->courier_name) }}" placeholder="Bekzod"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.courier_phone') }}</label>
                        <input type="text" name="courier_phone" value="{{ old('courier_phone', $order->courier_phone) }}" placeholder="+998 93 777-11-22"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                    <div>
                        <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.estimated_delivery') }}</label>
                        <input type="text" name="estimated_delivery_text" value="{{ old('estimated_delivery_text', $order->estimated_delivery_text) }}" placeholder="20 noyabr"
                               class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
                    </div>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.wish_full_label') }}</label>
                    <textarea name="wish" rows="2"
                              class="w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] p-[12px] text-[13px]">{{ old('wish', $order->wish) }}</textarea>
                </div>

                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.orders.admin_note_label') }}</label>
                    <textarea name="admin_note" rows="2" placeholder="{{ __('admin.orders.admin_note_placeholder') }}"
                              class="w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] p-[12px] text-[13px]">{{ old('admin_note', $order->admin_note) }}</textarea>
                </div>

                <div class="flex justify-end pt-[4px]">
                    <button type="submit" class="rounded-[12px] bg-[#227bfc] px-[24px] py-[12px] text-[14px] font-semibold text-white hover:bg-[#1a68db]">
                        {{ __('admin.orders.save_order_btn') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Right column: Items & Financials --}}
        <div class="flex flex-col gap-[20px]">
            {{-- Order Items --}}
            <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
                <h2 class="mb-[14px] text-[16px] font-bold text-ink">{{ __('admin.orders.order_items_title', ['count' => $order->items->count()]) }}</h2>
                <div class="flex flex-col gap-[12px]">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-[12px] rounded-[14px] bg-[#f8fafc] p-[10px]">
                            <img src="{{ asset($item->image) }}" alt="" class="h-[56px] w-[46px] shrink-0 rounded-[10px] bg-white object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-ink">{{ $item->title }}</p>
                                <p class="text-[12px] text-[#737373]">{{ $item->qty_label }} × {{ number_format($item->unit_price_rub, 0, '.', ' ') }} ₽</p>
                            </div>
                            <p class="shrink-0 text-[14px] font-bold text-ink">{{ number_format($item->total_price_rub, 0, '.', ' ') }} ₽</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Financial summary --}}
            <div class="rounded-[20px] bg-white p-[20px] shadow-2xs">
                <h2 class="mb-[14px] text-[16px] font-bold text-ink">{{ __('admin.orders.financial_title') }}</h2>
                <div class="flex flex-col gap-[10px] text-[13px]">
                    <div class="flex justify-between">
                        <span class="text-[#737373]">{{ __('admin.orders.payment_method_row') }}</span>
                        <span class="font-semibold text-ink">{{ strtoupper($order->payment_method) }} ({{ $order->card_masked }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#737373]">{{ __('admin.orders.exchange_rate_row') }}</span>
                        <span class="font-semibold text-[#227bfc]">1 RUB = {{ $order->exchange_rate }} UZS</span>
                    </div>
                    <hr class="border-[#eef2f6]">
                    <div class="flex justify-between">
                        <span class="text-[#737373]">{{ __('admin.orders.items_sum_row') }}</span>
                        <span class="font-medium text-ink">{{ number_format($order->items_total_rub, 0, '.', ' ') }} ₽</span>
                    </div>
                    @if ($order->discount_rub > 0)
                        <div class="flex justify-between">
                            <span class="text-[#737373]">{{ __('admin.orders.discount_row') }}</span>
                            <span class="font-medium text-[#12b76a]">−{{ number_format($order->discount_rub, 0, '.', ' ') }} ₽</span>
                        </div>
                    @endif
                    @if ($order->oversize_fee_rub > 0)
                        <div class="flex justify-between">
                            <span class="text-[#737373]">{{ __('admin.orders.oversize_row') }}</span>
                            <span class="font-medium text-ink">{{ number_format($order->oversize_fee_rub, 0, '.', ' ') }} ₽</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-[#737373]">{{ __('admin.orders.delivery_row', ['region' => $order->region]) }}</span>
                        <span class="font-medium text-[#12b76a]">{{ $order->delivery_fee_rub > 0 ? number_format($order->delivery_fee_rub, 0, '.', ' ').' ₽' : __('admin.free') }}</span>
                    </div>
                    <hr class="border-[#eef2f6]">
                    <div class="flex items-baseline justify-between pt-[4px]">
                        <span class="text-[15px] font-bold text-ink">{{ __('admin.orders.total_paid_row') }}</span>
                        <div class="text-right">
                            <p class="text-[18px] font-bold text-ink">{{ $order->formatted_total }}</p>
                            <p class="text-[11px] text-[#737373]">≈ {{ $order->formatted_total_uzs }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
