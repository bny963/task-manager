<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Task App' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 m-0 p-0"> {{-- 余白をリセット --}}
    <main class="w-full"> {{-- 幅を100%に固定 --}}
        {{ $slot }}
    </main>

    @stack('scripts')
</body>

</html>