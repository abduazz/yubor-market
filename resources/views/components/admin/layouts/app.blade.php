@props(['title' => null, 'heading' => null, 'subheading' => null])
@php
    $currentLocale = app()->getLocale();
    $pageTitle = $title ?? __('admin.panel_title');
    $pageHeading = $heading ?? __('admin.nav.dashboard');

    $activeOrdersBadge = 0;
    try {
        $activeOrdersBadge = \App\Models\Order::query()->whereIn('status', ['paid', 'packing', 'shipping'])->count();
    } catch (\Throwable) {}

    $navItems = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => __('admin.nav.dashboard'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>'],
        ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => __('admin.nav.orders'), 'badge' => $activeOrdersBadge, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>'],
        ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => __('admin.nav.products'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>'],
        ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => __('admin.nav.categories'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>'],
        ['route' => 'admin.banners.index', 'pattern' => 'admin.banners.*', 'label' => __('admin.nav.banners'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>'],
        ['route' => 'admin.regions.index', 'pattern' => 'admin.regions.*', 'label' => __('admin.nav.regions'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>'],
        ['route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'label' => __('admin.nav.settings'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f1f5f8] font-sans text-ink antialiased">
    <div class="flex min-h-dvh w-full">
        {{-- Sidebar --}}
        <aside class="sticky top-0 hidden h-dvh w-[264px] shrink-0 flex-col justify-between border-r border-[#dbe4ee] bg-white px-[16px] py-[20px] lg:flex">
            <div class="flex flex-col gap-[24px]">
                <div class="flex items-center justify-between px-[8px]">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-[8px]">
                        <img src="{{ asset('img/logo-mark.svg') }}" alt="" class="h-[26px] w-[28px]">
                        <img src="{{ asset('img/logo-text.svg') }}" alt="YUBOR" class="h-[18px] w-[74px]">
                        <span class="rounded-full bg-[#227bfc] px-[7px] py-[2px] text-[10px] font-semibold tracking-wide text-white">{{ __('admin.brand_admin') }}</span>
                    </a>
                </div>

                <nav class="flex flex-col gap-[4px]">
                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center justify-between rounded-[12px] px-[12px] py-[10px] text-[14px] font-medium transition-colors {{ $active ? 'bg-[#227bfc] text-white shadow-xs' : 'text-[#525252] hover:bg-[#f1f5f8] hover:text-ink' }}">
                            <span class="flex items-center gap-[10px]">
                                <svg class="size-[20px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $item['icon'] !!}</svg>
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if (!empty($item['badge']))
                                <span class="rounded-full px-[7px] py-[1px] text-[11px] font-semibold {{ $active ? 'bg-white/20 text-white' : 'bg-[#d0e7fd] text-[#227bfc]' }}">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="flex flex-col gap-[10px] border-t border-[#eef2f6] pt-[16px]">
                <a href="{{ route('home') }}" target="_blank"
                   class="flex items-center justify-between rounded-[12px] bg-[#f1f5f8] px-[12px] py-[10px] text-[13px] font-medium text-ink transition hover:bg-[#e3ebf2]">
                    <span class="flex items-center gap-[8px]">
                        <span class="size-[8px] rounded-full bg-[#12b76a]"></span>
                        <span>{{ __('admin.open_storefront') }}</span>
                    </span>
                    <svg class="size-[16px] text-[#737373]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>

                <div class="flex items-center justify-between px-[8px] pt-[4px]">
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-semibold text-ink">{{ auth()->user()?->name ?? 'Admin' }}</p>
                        <p class="truncate text-[11px] text-[#737373]">{{ auth()->user()?->email }}</p>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="{{ __('admin.logout') }}"
                                class="rounded-[10px] p-[8px] text-[#737373] transition hover:bg-[#fee4e2] hover:text-[#d92d20]">
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Mobile top nav --}}
            <div class="flex flex-col border-b border-[#dbe4ee] bg-white px-[16px] py-[12px] lg:hidden">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-[8px]">
                        <img src="{{ asset('img/logo-mark.svg') }}" alt="" class="h-[24px] w-[26px]">
                        <span class="font-semibold text-ink">Yubor Admin</span>
                    </a>
                    <div class="flex items-center gap-[8px]">
                        <div class="inline-flex items-center rounded-[10px] bg-[#f1f5f8] p-[2px]">
                            <a href="{{ route('admin.locale', 'uz') }}"
                               class="rounded-[8px] px-[8px] py-[4px] text-[11px] font-semibold transition {{ $currentLocale === 'uz' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373]' }}">
                                O‘Z
                            </a>
                            <a href="{{ route('admin.locale', 'ru') }}"
                               class="rounded-[8px] px-[8px] py-[4px] text-[11px] font-semibold transition {{ $currentLocale === 'ru' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373]' }}">
                                РУ
                            </a>
                        </div>
                        <a href="{{ route('home') }}" target="_blank" class="rounded-[10px] bg-[#f1f5f8] px-[10px] py-[6px] text-[12px] font-medium text-ink">{{ __('admin.storefront_short') }}</a>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-[10px] bg-[#fee4e2] px-[10px] py-[6px] text-[12px] font-medium text-[#d92d20]">{{ __('admin.logout') }}</button>
                        </form>
                    </div>
                </div>
                <div class="no-scrollbar mt-[10px] flex gap-[6px] overflow-x-auto pb-[2px]">
                    @foreach ($navItems as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="shrink-0 rounded-[10px] px-[10px] py-[6px] text-[12px] font-medium {{ $active ? 'bg-[#227bfc] text-white' : 'bg-[#f1f5f8] text-[#525252]' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Page header --}}
            <header class="flex flex-wrap items-center justify-between gap-[16px] border-b border-[#dbe4ee] bg-white px-[24px] py-[18px]">
                <div>
                    <h1 class="text-[22px] leading-tight font-bold text-ink">{{ $pageHeading }}</h1>
                    @if ($subheading)
                        <p class="mt-[2px] text-[13px] text-[#737373]">{{ $subheading }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-[10px]">
                    {{-- Language Switcher (Desktop & Tablet) --}}
                    <div class="hidden items-center rounded-[12px] border border-[#dbe4ee] bg-[#f1f5f8] p-[3px] lg:inline-flex" role="group" aria-label="Language switcher">
                        <a href="{{ route('admin.locale', 'uz') }}"
                           class="flex items-center gap-[5px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition {{ $currentLocale === 'uz' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373] hover:text-ink' }}">
                            <span>🇺🇿</span>
                            <span>O‘zbekcha</span>
                        </a>
                        <a href="{{ route('admin.locale', 'ru') }}"
                           class="flex items-center gap-[5px] rounded-[9px] px-[10px] py-[5px] text-[12px] font-semibold transition {{ $currentLocale === 'ru' ? 'bg-white text-[#227bfc] shadow-2xs' : 'text-[#737373] hover:text-ink' }}">
                            <span>🇷🇺</span>
                            <span>Русский</span>
                        </a>
                    </div>

                    {{ $actions ?? '' }}
                </div>
            </header>

            {{-- Flash messages & Content --}}
            <main class="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-[20px] p-[24px]">
                @if (session('success'))
                    <div class="flex items-center justify-between rounded-[16px] border border-[#a6f4c5] bg-[#ecfdf3] px-[16px] py-[12px] text-[14px] font-medium text-[#027a48]">
                        <div class="flex items-center gap-[10px]">
                            <img src="{{ asset('img/icon-check-big.svg') }}" alt="" class="size-[20px]">
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-[16px] border border-[#fecdca] bg-[#fef3f2] px-[16px] py-[12px] text-[13px] text-[#b42318]">
                        <p class="font-semibold">{{ __('admin.fix_errors') }}</p>
                        <ul class="mt-[4px] list-inside list-disc">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
