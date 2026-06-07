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
                            sans: ['Vazirmatn', 'Segoe UI', 'Tahoma', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                DEFAULT: '#0d9488',
                                dark: '#0f766e',
                                soft: '#f0fdfa',
                            },
                            accent: {
                                DEFAULT: '#d97706',
                                soft: '#fffbeb',
                            },
                            ink: {
                                DEFAULT: '#0f172a',
                                muted: '#64748b',
                            },
                            surface: '#f1f5f9',
                            line: '#e2e8f0',
                        },
                        borderRadius: {
                            card: '1rem',
                        },
                        boxShadow: {
                            card: '0 1px 3px 0 rgb(15 23 42 / 0.06), 0 1px 2px -1px rgb(15 23 42 / 0.06)',
                            'card-hover': '0 10px 25px -5px rgb(15 23 42 / 0.08), 0 4px 10px -4px rgb(15 23 42 / 0.04)',
                        },
                    },
                },
            };
        </script>
        <style type="text/tailwindcss">
            @layer components {
                .ps-container { @apply mx-auto w-full max-w-6xl px-4 sm:px-6; }
                .ps-card { @apply rounded-2xl border border-line bg-white shadow-card; }
                .ps-card-interactive { @apply ps-card transition duration-200 hover:border-brand/20 hover:shadow-card-hover; }
                .ps-btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition duration-150; }
                .ps-btn-primary { @apply ps-btn bg-brand text-white hover:bg-brand-dark active:scale-[0.98]; }
                .ps-btn-secondary { @apply ps-btn border border-line bg-white text-ink hover:border-brand/30 hover:bg-brand-soft/50; }
                .ps-section-label { @apply text-xs font-semibold uppercase tracking-wider text-brand; }
                .ps-section-title { @apply text-xl font-bold text-ink sm:text-2xl; }
                .ps-prose { @apply space-y-3 text-sm leading-7 text-ink-muted; }
                .ps-prose :is(h2,h3,h4) { @apply font-semibold text-ink; }
                .ps-prose :is(ul,ol) { @apply pr-5; }
                .ps-prose ul { @apply list-disc; }
                .ps-prose ol { @apply list-decimal; }
                .ps-expandable-content { @apply overflow-hidden; }
                .ps-expandable:not(.is-expanded) .ps-expandable-content { @apply line-clamp-5; }
                .ps-expandable:not(.is-expanded)::after { content: ''; @apply pointer-events-none absolute inset-x-0 bottom-10 h-16 bg-gradient-to-t from-white to-transparent; }
                .ps-expandable-toggle { @apply relative z-10 mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-line bg-surface py-2.5 text-sm font-medium text-brand transition hover:border-brand/30 hover:bg-brand-soft; }
                .ps-expandable-toggle[hidden] { display: none; }
            }
        </style>
    @endif
    @stack('head')
</head>
<body class="flex min-h-screen flex-col font-sans antialiased">
    <header class="sticky top-0 z-40 border-b border-line/80 bg-white/90 backdrop-blur-md">
        <div class="ps-container flex h-16 items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-xl bg-brand text-sm font-bold text-white shadow-sm">پ</span>
                <span class="text-base font-bold text-ink">{{ config('app.name', 'پارتس‌مال') }}</span>
            </a>
            <nav class="flex items-center gap-1 text-sm text-ink-muted">
                <a href="#" class="hidden rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink sm:inline">قطعات</a>
                <a href="#" class="hidden rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink sm:inline">فروشگاه‌ها</a>
                <a href="#" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">درباره ما</a>
                <a href="#" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">تماس</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 py-8 sm:py-10">
        <div class="ps-container">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-line bg-white">
        <div class="ps-container py-10">
            <div class="grid gap-8 sm:grid-cols-3">
                <div>
                    <p class="font-bold text-ink">{{ config('app.name', 'پارتس‌مال') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-ink-muted">مرجع لوازم یدکی و اطلاعات تعمیر خودرو — placeholder</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">دسترسی سریع</p>
                    <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                        <li><a href="#" class="transition hover:text-brand">قطعات</a></li>
                        <li><a href="#" class="transition hover:text-brand">فروشگاه‌ها</a></li>
                        <li><a href="#" class="transition hover:text-brand">تماس با ما</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">تماس</p>
                    <p class="mt-3 text-sm text-ink-muted">placeholder@example.com</p>
                </div>
            </div>
            <div class="mt-8 border-t border-line pt-6 text-center text-xs text-ink-muted/80">
                &copy; {{ date('Y') }} {{ config('app.name', 'پارتس‌مال') }}. تمامی حقوق محفوظ است.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
