@props(['title' => 'Yubor Market', 'screenClass' => ''])
<!DOCTYPE html>
<html lang="uz" data-script="{{ $storefrontScript ?? 'lat' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'Yubor Market' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-screen {{ $screenClass ?? '' }}">
        {{ $slot }}
    </div>

    @stack('modals')
</body>
</html>
