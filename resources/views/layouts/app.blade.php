<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'پارتس‌مال'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Vazirmatn', 'Tahoma', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
    @endif
    @stack('head')
</head>
<body class="min-h-screen bg-stone-100 font-sans text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="text-lg font-bold text-amber-700">
                {{ config('app.name', 'پارتس‌مال') }}
            </a>
            <nav class="flex items-center gap-4 text-sm text-stone-600">
                <span class="hidden sm:inline">هدر — جایگاه منوی اصلی</span>
                <a href="#" class="rounded-lg px-3 py-1.5 transition hover:bg-stone-100">درباره ما</a>
                <a href="#" class="rounded-lg px-3 py-1.5 transition hover:bg-stone-100">تماس</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    <footer class="mt-auto border-t border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-stone-500">فوتر — اطلاعات تماس و لینک‌های مفید (placeholder)</p>
                <p class="text-xs text-stone-400">&copy; {{ date('Y') }} {{ config('app.name', 'پارتس‌مال') }}</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
