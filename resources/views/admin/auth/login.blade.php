@php
    $currentLocale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.auth.page_title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh items-center justify-center bg-[#e5eaf0] p-[16px] font-sans text-ink antialiased">
    <div class="flex w-full max-w-[420px] flex-col gap-[22px] rounded-[28px] bg-white p-[32px] shadow-[0_12px_40px_rgba(26,51,102,0.08)]">
        {{-- Language switcher --}}
        <div class="flex justify-end">
            <div class="inline-flex items-center rounded-[12px] border border-[#dbe4ee] bg-[#f1f5f8] p-[3px]">
                <a href="{{ route('admin.locale', 'uz') }}"
                   class="flex items-center gap-[4px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition {{ $currentLocale === 'uz' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373] hover:text-ink' }}">
                    <span>🇺🇿</span>
                    <span>O‘zbekcha</span>
                </a>
                <a href="{{ route('admin.locale', 'ru') }}"
                   class="flex items-center gap-[4px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition {{ $currentLocale === 'ru' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373] hover:text-ink' }}">
                    <span>🇷🇺</span>
                    <span>Русский</span>
                </a>
            </div>
        </div>

        <div class="flex flex-col items-center gap-[12px] text-center">
            <div class="flex items-center gap-[8px]">
                <img src="{{ asset('img/logo-mark.svg') }}" alt="" class="h-[32px] w-[34px]">
                <img src="{{ asset('img/logo-text.svg') }}" alt="YUBOR" class="h-[22px] w-[90px]">
                <span class="rounded-full bg-[#227bfc] px-[8px] py-[3px] text-[12px] font-semibold text-white">{{ __('admin.brand_admin') }}</span>
            </div>
            <div>
                <h1 class="text-[22px] font-bold text-ink">{{ __('admin.auth.heading') }}</h1>
                <p class="mt-[4px] text-[13px] text-[#737373]">{{ __('admin.auth.subheading') }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-[14px] bg-[#fef3f2] px-[14px] py-[10px] text-[13px] font-medium text-[#d92d20]">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="flex flex-col gap-[16px]">
            @csrf
            <div class="flex flex-col gap-[6px]">
                <label for="email" class="text-[13px] font-medium text-[#525252]">{{ __('admin.auth.email_label') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'admin@yubor.uz') }}" required autofocus
                       class="h-[48px] rounded-[14px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[15px] text-ink outline-none transition focus:border-[#227bfc] focus:bg-white">
            </div>

            <div class="flex flex-col gap-[6px]">
                <label for="password" class="text-[13px] font-medium text-[#525252]">{{ __('admin.auth.password_label') }}</label>
                <input type="password" name="password" id="password" value="password" required
                       class="h-[48px] rounded-[14px] border border-[#dbe4ee] bg-[#f7f7f8] px-[14px] text-[15px] text-ink outline-none transition focus:border-[#227bfc] focus:bg-white">
            </div>

            <label class="flex cursor-pointer items-center gap-[8px] text-[13px] text-[#525252]">
                <input type="checkbox" name="remember" checked class="size-[16px] rounded accent-[#227bfc]">
                <span>{{ __('admin.auth.remember_me') }}</span>
            </label>

            <button type="submit"
                    class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-[#227bfc] text-[15px] font-semibold text-white transition hover:bg-[#1a68db] active:scale-[.99]">
                {{ __('admin.auth.login_btn') }}
            </button>
        </form>

        <div class="flex items-center justify-between border-t border-[#eef2f6] pt-[16px] text-[12px] text-[#737373]">
            <span>{{ __('admin.auth.demo_hint') }} <strong class="text-ink">admin@yubor.uz</strong> / <strong class="text-ink">password</strong></span>
            <a href="{{ route('home') }}" class="font-medium text-[#227bfc] hover:underline">{{ __('admin.back_to_storefront') }}</a>
        </div>
    </div>
</body>
</html>
