@php
    $oldCards = old('cards');
    $hasOldCards = is_array($oldCards);
    $cards = $hasOldCards ? $oldCards : ($receivingCards ?? []);
    if (empty($cards)) {
        $cards = [['number' => '', 'holder' => '', '_is_new' => true]];
    }
@endphp

<x-admin.layouts.app :heading="__('admin.settings.heading')" :subheading="__('admin.settings.subheading')">
    <div class="flex w-full flex-col gap-[20px]">
        {{-- Qabul qiluvchi kartalar (alohida forma va saqlash tugmasi) --}}
        <form id="settings-cards-form" action="{{ route('admin.settings.cards') }}" method="POST"
              class="flex w-full flex-col gap-[16px] rounded-[20px] bg-white p-[24px] shadow-2xs">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap items-center justify-between gap-[12px]">
                <div>
                    <h2 class="text-[17px] font-bold text-ink">{{ __('admin.settings.cards_title') }}</h2>
                    <p class="mt-[2px] text-[12px] text-[#737373]">{{ __('admin.settings.cards_sub') }}</p>
                </div>
                <button type="button" id="add-card-btn"
                        class="rounded-[12px] bg-[#e8f1fe] px-[14px] py-[9px] text-[13px] font-semibold text-[#227bfc] transition hover:bg-[#d0e7fd]">
                    {{ __('admin.settings.add_card_btn') }}
                </button>
            </div>

            <div id="receiving-cards-list" class="flex flex-col gap-[12px]">
                @foreach ($cards as $idx => $card)
                    @php
                        $rowHasError = $errors->has("cards.{$idx}.number");
                        $isSaved = ! $hasOldCards && empty($card['_is_new']) && ! empty($card['number']);
                        $isEditing = ! $isSaved || $rowHasError;
                    @endphp
                    <div class="flex flex-col gap-[10px] rounded-[16px] border border-[#dbe4ee] bg-[#f8fafc] p-[14px]"
                         data-card-row data-editing="{{ $isEditing ? 'true' : 'false' }}">
                        <div class="grid grid-cols-1 items-start gap-[12px] md:grid-cols-12">
                            <div class="md:col-span-5">
                                <label class="mb-[4px] flex items-center justify-between text-[12px] font-medium text-[#525252]">
                                    <span>{{ __('admin.settings.card_number_label') }}</span>
                                    <span data-card-badge class="hidden rounded-[6px] px-[7px] py-[2px] text-[10px] font-bold tracking-wide"></span>
                                </label>
                                <input type="text"
                                       name="cards[{{ $idx }}][number]"
                                       value="{{ $card['number'] ?? '' }}"
                                       inputmode="numeric"
                                       maxlength="19"
                                       autocomplete="off"
                                       placeholder=""
                                       data-card-number
                                       @readonly(! $isEditing)
                                       class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] px-[14px] font-mono text-[15px] font-semibold tracking-wider text-ink outline-none transition focus:border-[#227bfc] {{ $isEditing ? 'bg-white' : 'bg-[#f1f5f8] text-[#525252]' }}">
                                <p data-card-error class="mt-[5px] hidden text-[12px] font-medium text-[#d92d20]"></p>
                                @error("cards.{$idx}.number")
                                    <p class="mt-[5px] text-[12px] font-medium text-[#d92d20]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-5">
                                <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">
                                    {{ __('admin.settings.card_holder_label') }}
                                </label>
                                <input type="text"
                                       name="cards[{{ $idx }}][holder]"
                                       value="{{ $card['holder'] ?? '' }}"
                                       maxlength="80"
                                       placeholder=""
                                       data-card-holder
                                       @readonly(! $isEditing)
                                       class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] px-[14px] text-[13px] uppercase text-ink outline-none transition focus:border-[#227bfc] {{ $isEditing ? 'bg-white' : 'bg-[#f1f5f8] text-[#525252]' }}">
                            </div>

                            <div class="flex items-end justify-end gap-[8px] md:col-span-2 md:pt-[22px]">
                                <button type="button" data-edit-card
                                        class="h-[44px] flex-1 rounded-[12px] px-[12px] text-[12px] font-semibold transition {{ $isEditing ? 'hidden' : 'bg-[#e8f1fe] text-[#227bfc] hover:bg-[#d0e7fd]' }}">
                                    {{ __('admin.edit') }}
                                </button>
                                <button type="button" data-remove-card title="{{ __('admin.delete') }}"
                                        class="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-[12px] bg-[#fee4e2] text-[14px] font-semibold text-[#d92d20] transition hover:bg-[#fecdca]">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p id="no-cards-hint" class="hidden rounded-[14px] border border-dashed border-[#dbe4ee] py-[18px] text-center text-[13px] text-[#737373]">
                {{ __('admin.settings.empty_cards_hint') }}
            </p>

            <div class="flex justify-end border-t border-[#eef2f6] pt-[14px]">
                <button type="submit"
                        class="rounded-[12px] bg-[#227bfc] px-[24px] py-[11px] text-[14px] font-semibold text-white transition hover:bg-[#1a68db]">
                    {{ __('admin.settings.save_cards_btn') }}
                </button>
            </div>
        </form>

        {{-- Delivery texts & support --}}
        <form action="{{ route('admin.settings.update') }}" method="POST"
              class="flex w-full flex-col gap-[16px] rounded-[20px] bg-white p-[24px] shadow-2xs">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.settings.texts_title') }}</h2>
            </div>

            <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-2">
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.delivery_provider_label') }}</label>
                    <input type="text" name="delivery_provider_text" value="{{ old('delivery_provider_text', $settings['delivery_provider_text']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.delivery_badge_label') }}</label>
                    <input type="text" name="delivery_badge_text" value="{{ old('delivery_badge_text', $settings['delivery_badge_text']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.support_url_label') }}</label>
                    <input type="text" name="support_chat_url" value="{{ old('support_chat_url', $settings['support_chat_url']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.support_phone_label') }}</label>
                    <input type="text" name="marketplace_phone" value="{{ old('marketplace_phone', $settings['marketplace_phone']) }}"
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px] outline-none focus:border-[#227bfc] focus:bg-white">
                </div>
            </div>

            <div class="flex justify-end border-t border-[#eef2f6] pt-[14px]">
                <button type="submit" class="rounded-[12px] bg-[#227bfc] px-[24px] py-[11px] text-[14px] font-semibold text-white transition hover:bg-[#1a68db]">
                    {{ __('admin.settings.save_settings_btn') }}
                </button>
            </div>
        </form>
    </div>

    <template id="card-row-template">
        <div class="flex flex-col gap-[10px] rounded-[16px] border border-[#dbe4ee] bg-[#f8fafc] p-[14px]" data-card-row data-editing="true">
            <div class="grid grid-cols-1 items-start gap-[12px] md:grid-cols-12">
                <div class="md:col-span-5">
                    <label class="mb-[4px] flex items-center justify-between text-[12px] font-medium text-[#525252]">
                        <span>{{ __('admin.settings.card_number_label') }}</span>
                        <span data-card-badge class="hidden rounded-[6px] px-[7px] py-[2px] text-[10px] font-bold tracking-wide"></span>
                    </label>
                    <input type="text"
                           name="cards[__IDX__][number]"
                           value=""
                           inputmode="numeric"
                           maxlength="19"
                           autocomplete="off"
                           placeholder=""
                           data-card-number
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-white px-[14px] font-mono text-[15px] font-semibold tracking-wider text-ink outline-none transition focus:border-[#227bfc]">
                    <p data-card-error class="mt-[5px] hidden text-[12px] font-medium text-[#d92d20]"></p>
                </div>

                <div class="md:col-span-5">
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">
                        {{ __('admin.settings.card_holder_label') }}
                    </label>
                    <input type="text"
                           name="cards[__IDX__][holder]"
                           value=""
                           maxlength="80"
                           placeholder=""
                           data-card-holder
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-white px-[14px] text-[13px] uppercase text-ink outline-none transition focus:border-[#227bfc]">
                </div>

                <div class="flex items-end justify-end gap-[8px] md:col-span-2 md:pt-[22px]">
                    <button type="button" data-edit-card
                            class="hidden h-[44px] flex-1 rounded-[12px] bg-[#e8f1fe] px-[12px] text-[12px] font-semibold text-[#227bfc] transition hover:bg-[#d0e7fd]">
                        {{ __('admin.edit') }}
                    </button>
                    <button type="button" data-remove-card title="{{ __('admin.delete') }}"
                            class="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-[12px] bg-[#fee4e2] text-[14px] font-semibold text-[#d92d20] transition hover:bg-[#fecdca]">
                        ✕
                    </button>
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            const MESSAGES = {
                required: @json(__('admin.settings.card_required')),
                invalid: @json(__('admin.settings.card_invalid')),
                duplicate: @json(__('admin.settings.card_duplicate')),
            };

            const UZCARD_PREFIX = /^(8600|5614|5440|6262)/;
            const HUMO_PREFIX = /^9860/;
            const FULL_CARD_REGEX = /^(8600|5614|5440|6262|9860)\d{12}$/;

            const form = document.getElementById('settings-cards-form');
            const list = document.getElementById('receiving-cards-list');
            const addBtn = document.getElementById('add-card-btn');
            const template = document.getElementById('card-row-template');
            const emptyHint = document.getElementById('no-cards-hint');

            let nextIndex = list.querySelectorAll('[data-card-row]').length;

            const formatCard = (val) => {
                const digits = val.replace(/\D/g, '').slice(0, 16);
                return digits.replace(/(\d{4})(?=\d)/g, '$1 ');
            };

            const detectNetwork = (digits) => {
                if (UZCARD_PREFIX.test(digits)) return 'uzcard';
                if (HUMO_PREFIX.test(digits)) return 'humo';
                return null;
            };

            const updateEmptyState = () => {
                const hasRows = list.querySelectorAll('[data-card-row]').length > 0;
                emptyHint.classList.toggle('hidden', hasRows);
            };

            const setEditing = (row, editing) => {
                row.dataset.editing = editing ? 'true' : 'false';
                const numInput = row.querySelector('[data-card-number]');
                const holderInput = row.querySelector('[data-card-holder]');
                const editBtn = row.querySelector('[data-edit-card]');

                [numInput, holderInput].forEach((inp) => {
                    if (!inp) return;
                    inp.readOnly = !editing;
                    inp.classList.toggle('bg-white', editing);
                    inp.classList.toggle('bg-[#f1f5f8]', !editing);
                    inp.classList.toggle('text-[#525252]', !editing);
                });

                if (editBtn) {
                    editBtn.classList.toggle('hidden', editing);
                }

                if (editing && numInput) {
                    numInput.focus();
                }
            };

            const validateRow = (row, showErrors) => {
                const numInput = row.querySelector('[data-card-number]');
                const holderInput = row.querySelector('[data-card-holder]');
                const errEl = row.querySelector('[data-card-error]');
                const badgeEl = row.querySelector('[data-card-badge]');

                const digits = numInput.value.replace(/\D/g, '');
                const holder = (holderInput?.value || '').trim();
                const network = detectNetwork(digits);

                if (network === 'uzcard') {
                    badgeEl.textContent = digits.length === 16 ? 'UZCARD ✓' : 'UZCARD';
                    badgeEl.className = 'rounded-[6px] bg-[#1e5eff] px-[7px] py-[2px] text-[10px] font-bold tracking-wide text-white';
                } else if (network === 'humo') {
                    badgeEl.textContent = digits.length === 16 ? 'HUMO ✓' : 'HUMO';
                    badgeEl.className = 'rounded-[6px] bg-[#0fa958] px-[7px] py-[2px] text-[10px] font-bold tracking-wide text-white';
                } else {
                    badgeEl.className = 'hidden';
                    badgeEl.textContent = '';
                }

                if (digits.length === 0 && holder === '') {
                    numInput.classList.remove('border-[#f04438]', 'bg-[#fef3f2]', 'border-[#12b76a]');
                    errEl.classList.add('hidden');
                    errEl.textContent = '';
                    return true;
                }

                let errorMsg = '';
                if (digits.length === 0 && holder !== '') {
                    errorMsg = MESSAGES.required;
                } else if (!FULL_CARD_REGEX.test(digits)) {
                    errorMsg = MESSAGES.invalid;
                } else {
                    const allDigits = Array.from(list.querySelectorAll('[data-card-number]'))
                        .map((el) => el.value.replace(/\D/g, ''))
                        .filter((d) => d.length > 0);
                    if (allDigits.filter((d) => d === digits).length > 1) {
                        errorMsg = MESSAGES.duplicate;
                    }
                }

                if (!errorMsg) {
                    numInput.classList.remove('border-[#f04438]', 'bg-[#fef3f2]');
                    numInput.classList.add('border-[#12b76a]');
                    errEl.classList.add('hidden');
                    errEl.textContent = '';
                    return true;
                }

                if (showErrors) {
                    setEditing(row, true);
                    numInput.classList.remove('border-[#12b76a]');
                    numInput.classList.add('border-[#f04438]', 'bg-[#fef3f2]');
                    errEl.textContent = errorMsg;
                    errEl.classList.remove('hidden');
                }
                return false;
            };

            const bindRow = (row) => {
                const numInput = row.querySelector('[data-card-number]');
                const editBtn = row.querySelector('[data-edit-card]');
                const removeBtn = row.querySelector('[data-remove-card]');

                numInput.value = formatCard(numInput.value);
                if (numInput.value) validateRow(row, false);

                numInput.addEventListener('input', () => {
                    if (numInput.readOnly) return;
                    numInput.value = formatCard(numInput.value);
                    const digits = numInput.value.replace(/\D/g, '');
                    const errVisible = !row.querySelector('[data-card-error]').classList.contains('hidden');
                    const badPrefixEarly = digits.length >= 4 && !detectNetwork(digits);
                    validateRow(row, errVisible || badPrefixEarly || digits.length === 16);
                });

                numInput.addEventListener('blur', () => {
                    if (!numInput.readOnly) validateRow(row, true);
                });

                editBtn?.addEventListener('click', () => {
                    setEditing(row, true);
                });

                removeBtn.addEventListener('click', () => {
                    row.remove();
                    updateEmptyState();
                });
            };

            list.querySelectorAll('[data-card-row]').forEach(bindRow);
            updateEmptyState();

            addBtn?.addEventListener('click', () => {
                const html = template.innerHTML.replace(/__IDX__/g, String(nextIndex++));
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const row = wrapper.firstElementChild;
                list.appendChild(row);
                bindRow(row);
                updateEmptyState();
                row.querySelector('[data-card-number]')?.focus();
            });

            form?.addEventListener('submit', (e) => {
                let firstBad = null;
                list.querySelectorAll('[data-card-row]').forEach((row) => {
                    if (!validateRow(row, true) && !firstBad) {
                        firstBad = row.querySelector('[data-card-number]');
                    }
                });
                if (firstBad) {
                    e.preventDefault();
                    firstBad.focus();
                }
            });
        })();
    </script>
</x-admin.layouts.app>
