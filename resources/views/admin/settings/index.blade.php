<x-admin.layouts.app :heading="__('admin.settings.heading')" :subheading="__('admin.settings.subheading')">
    <div class="grid grid-cols-1 gap-[20px] xl:grid-cols-3">
        {{-- Marketplace Settings --}}
        <form action="{{ route('admin.settings.update') }}" method="POST"
              class="flex flex-col gap-[20px] rounded-[20px] bg-white p-[24px] shadow-2xs xl:col-span-2">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.settings.rates_title') }}</h2>
                <p class="text-[12px] text-[#737373]">{{ __('admin.settings.rates_sub') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-2">
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.rate_card_label') }}</label>
                    <input type="text" name="rate_card_uzs" value="{{ old('rate_card_uzs', $settings['rate_card_uzs']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[14px] font-bold text-ink">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.rate_sbp_label') }}</label>
                    <input type="text" name="rate_sbp_uzs" value="{{ old('rate_sbp_uzs', $settings['rate_sbp_uzs']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[14px] font-bold text-[#227bfc]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.gift_wrap_label') }}</label>
                    <input type="number" name="gift_wrap_fee_rub" value="{{ old('gift_wrap_fee_rub', $settings['gift_wrap_fee_rub']) }}" required min="0"
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[14px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.oversize_label') }}</label>
                    <input type="number" name="oversize_fee_rub" value="{{ old('oversize_fee_rub', $settings['oversize_fee_rub']) }}" required min="0"
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[14px]">
                </div>
            </div>

            <hr class="border-[#eef2f6]">

            <div>
                <h2 class="text-[16px] font-bold text-ink">{{ __('admin.settings.texts_title') }}</h2>
            </div>

            <div class="grid grid-cols-1 gap-[14px] sm:grid-cols-2">
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.delivery_provider_label') }}</label>
                    <input type="text" name="delivery_provider_text" value="{{ old('delivery_provider_text', $settings['delivery_provider_text']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.delivery_badge_label') }}</label>
                    <input type="text" name="delivery_badge_text" value="{{ old('delivery_badge_text', $settings['delivery_badge_text']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.support_url_label') }}</label>
                    <input type="text" name="support_chat_url" value="{{ old('support_chat_url', $settings['support_chat_url']) }}" required
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px]">
                </div>
                <div>
                    <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.support_phone_label') }}</label>
                    <input type="text" name="marketplace_phone" value="{{ old('marketplace_phone', $settings['marketplace_phone']) }}"
                           class="h-[44px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[13px]">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-[12px] bg-[#227bfc] px-[24px] py-[12px] text-[14px] font-semibold text-white hover:bg-[#1a68db]">
                    {{ __('admin.settings.save_settings_btn') }}
                </button>
            </div>
        </form>

        {{-- Admin Profile --}}
        <form action="{{ route('admin.settings.profile') }}" method="POST"
              class="flex h-fit flex-col gap-[14px] rounded-[20px] bg-white p-[24px] shadow-2xs">
            @csrf
            @method('PUT')
            <h2 class="text-[16px] font-bold text-ink">{{ __('admin.settings.profile_title') }}</h2>

            <div>
                <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.admin_name_label') }}</label>
                <input type="text" name="name" value="{{ old('name', $admin?->name) }}" required
                       class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
            </div>
            <div>
                <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.admin_email_label') }}</label>
                <input type="email" name="email" value="{{ old('email', $admin?->email) }}" required
                       class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
            </div>
            <div>
                <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.new_password_label') }}</label>
                <input type="password" name="new_password"
                       class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
            </div>
            <div>
                <label class="mb-[4px] block text-[12px] font-medium text-[#525252]">{{ __('admin.settings.confirm_password_label') }}</label>
                <input type="password" name="new_password_confirmation"
                       class="h-[42px] w-full rounded-[12px] border border-[#dbe4ee] bg-[#f7f7f8] px-[12px] text-[13px]">
            </div>

            <button type="submit" class="mt-[4px] h-[44px] rounded-[12px] bg-[#1c1917] text-[13px] font-semibold text-white hover:bg-[#333]">
                {{ __('admin.settings.update_profile_btn') }}
            </button>
        </form>
    </div>
</x-admin.layouts.app>
