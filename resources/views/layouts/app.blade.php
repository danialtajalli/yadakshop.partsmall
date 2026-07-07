<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title??config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{asset('storage/img/favicon.webp')}}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('storage/img/favicon.webp')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('storage/img/favicon.webp')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('storage/img/favicon.webp')}}">
    <link rel="manifest" href="{{asset('storage/img/favicon.webp')}}">
    @php($themeColor = config('partsmall.theme_color', '#3f4857'))
    <link rel="mask-icon" href="{{ asset('storage/img/favicon.webp') }}" color="{{ $themeColor }}">
    <link rel="shortcut icon" href="{{ asset('storage/img/favicon.webp') }}">
    <meta name="msapplication-TileColor" content="{{ $themeColor }}">
    <meta name="msapplication-config" content="{{ asset('storage/img/favicon.webp') }}">
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
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
                                DEFAULT: '#3f4857',
                                dark: '#222c3d',
                                soft: '#f27c22',
                            },
                            accent: {
                                DEFAULT: '#3f4857',
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
                .ps-scrollbar { scrollbar-width: thin; scrollbar-color: rgb(148 163 184 / 0.5) transparent; }
                .ps-scrollbar::-webkit-scrollbar { width: 6px; }
                .ps-scrollbar::-webkit-scrollbar-track { margin-block: 0.5rem; background: transparent; }
                .ps-scrollbar::-webkit-scrollbar-thumb { border: 2px solid transparent; border-radius: 9999px; background-color: rgb(226 232 240 / 0.95); background-clip: padding-box; transition: background-color 150ms ease; }
                .ps-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgb(148 163 184 / 0.75); }
                .ps-prose { @apply space-y-3 text-sm leading-7 text-ink-muted; }
                .ps-prose :is(h2,h3,h4) { @apply font-semibold text-ink; }
                .ps-prose :is(ul,ol) { @apply pr-5; }
                .ps-prose ul { @apply list-disc; }
                .ps-prose ol { @apply list-decimal; }
                .ps-expandable-content { @apply overflow-hidden; }
                .ps-expandable:not(.is-expanded):not(.is-compact) .ps-expandable-content { @apply line-clamp-5; }
                .ps-expandable.is-truncated:not(.is-expanded)::after { content: ''; @apply pointer-events-none absolute inset-x-0 bottom-10 h-16 bg-gradient-to-t from-white to-transparent; }
                .ps-expandable-toggle { @apply relative z-10 mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-line bg-surface py-2.5 text-sm font-medium text-brand transition hover:border-brand/30 hover:bg-brand-soft; }
                .ps-expandable-toggle[hidden] { display: none; }
                .ps-shops-jump { animation: ps-shops-jump-enter 0.5s ease-out both, ps-shops-jump-glow 2.4s ease-in-out 0.5s infinite; }
                .ps-shops-jump-chevron { animation: ps-shops-jump-nudge 1.5s ease-in-out infinite; }
                .ps-shops-jump-chevron--up { animation: ps-shops-jump-nudge-up 1.5s ease-in-out infinite; }
                .ps-shops-jump.is-scrolling { animation: ps-shops-jump-tap 0.35s ease-out; }
                .ps-shops-jump.is-scrolling .ps-shops-jump-chevron { animation: ps-shops-jump-chevron-scroll 0.55s ease-in-out infinite; }
                .ps-shops-jump.is-scrolling .ps-shops-jump-chevron--up { animation: ps-shops-jump-chevron-scroll-up 0.55s ease-in-out infinite; }
                .ps-shops-section--highlight { animation: ps-shops-section-reveal 0.9s ease-out; }
                .ps-carousel { @apply relative; }
                .ps-carousel-viewport { @apply w-full overflow-hidden; direction: ltr; cursor: grab; touch-action: pan-y pinch-zoom; user-select: none; }
                .ps-carousel-viewport.is-dragging { cursor: grabbing; }
                .ps-carousel-track { @apply flex; direction: ltr; margin-left: -1rem; }
                .ps-carousel-slide { @apply min-w-0 shrink-0 grow-0 basis-[9.5rem] pl-4 sm:basis-[11rem]; direction: rtl; }
                .ps-carousel-nav { @apply absolute top-1/2 z-10 flex size-10 -translate-y-1/2 items-center justify-center rounded-full border border-line bg-white text-sm text-ink shadow-card transition hover:border-brand/30 hover:bg-brand-soft hover:text-brand; }
                .ps-carousel-nav--prev { @apply -end-3 sm:-end-4; }
                .ps-carousel-nav--next { @apply -start-3 sm:-start-4; }
                @media (prefers-reduced-motion: reduce) {
                    .ps-shops-jump, .ps-shops-jump-chevron, .ps-shops-jump-chevron--up, .ps-shops-jump.is-scrolling, .ps-shops-jump.is-scrolling .ps-shops-jump-chevron, .ps-shops-jump.is-scrolling .ps-shops-jump-chevron--up, .ps-shops-section--highlight { animation: none; }
                    .ps-shops-jump-chevron--up { transform: rotate(180deg); }
                }
            }
            @keyframes ps-shops-jump-enter {
                from { opacity: 0; transform: translateY(-0.5rem); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes ps-shops-jump-glow {
                0%, 100% { box-shadow: 0 8px 30px rgb(242 124 34 / 0.14), 0 0 0 0 rgb(242 124 34 / 0.2); }
                50% { box-shadow: 0 12px 36px rgb(242 124 34 / 0.2), 0 0 0 6px rgb(242 124 34 / 0); }
            }
            @keyframes ps-shops-jump-nudge {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(3px); }
            }
            @keyframes ps-shops-jump-nudge-up {
                0%, 100% { transform: rotate(180deg) translateY(0); }
                50% { transform: rotate(180deg) translateY(-3px); }
            }
            @keyframes ps-shops-jump-tap {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(0.97); }
            }
            @keyframes ps-shops-jump-chevron-scroll {
                0%, 100% { transform: translateY(0); opacity: 1; }
                50% { transform: translateY(5px); opacity: 0.65; }
            }
            @keyframes ps-shops-jump-chevron-scroll-up {
                0%, 100% { transform: rotate(180deg) translateY(0); opacity: 1; }
                50% { transform: rotate(180deg) translateY(-5px); opacity: 0.65; }
            }
            @keyframes ps-shops-section-reveal {
                0% { outline: 2px solid rgb(13 148 136 / 0.45); outline-offset: 6px; }
                100% { outline: 2px solid transparent; outline-offset: 12px; }
            }
            .ps-comment-star { width: 0.875rem; height: 0.875rem; flex-shrink: 0; }
            .ps-comment-star--filled path { fill: #d4a017; }
            .ps-comment-star--empty path { fill: #e2e8f0; }
            .ps-comment-star-score { margin-inline-start: 0.375rem; font-size: 0.75rem; font-weight: 500; color: #64748b; }
            .ps-comment-rating-summary { display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; color: #64748b; }
            .ps-comment-rating-summary__icon { width: 1rem; height: 1rem; }
            .ps-comment-rating-summary__icon path { fill: #d4a017; }
            .ps-comment-rating-summary__value { font-weight: 600; color: #0f172a; }
            .ps-comment-star-picker [data-rating-star] { padding: 0.125rem; background: transparent; border: none; cursor: pointer; border-radius: 0.25rem; line-height: 0; }
            .ps-comment-star-picker [data-rating-star] [data-star-icon] { width: 1.375rem; height: 1.375rem; }
            .ps-comment-star-picker [data-rating-star] path { fill: #e2e8f0; }
            .ps-comment-star-picker [data-rating-star][data-star-active='true'] path { fill: #d4a017; }
            .ps-comment-star-picker--error { border-radius: 0.5rem; outline: 1px solid #f0d4d4; outline-offset: 2px; }
            .ps-comment-rating-label--selected { color: #0f172a; }
            .ps-input--error { border-color: #e8b4b4; }
            .ps-form-errors { margin-bottom: 1rem; border-radius: 0.75rem; border: 1px solid #f0d4d4; background: #fdfafa; padding: 0.875rem 1rem; }
            .ps-form-errors__title { margin-bottom: 0.5rem; font-size: 0.8125rem; font-weight: 500; color: #9f1239; }
            .ps-form-errors__list { margin: 0; padding: 0; list-style: none; }
            .ps-form-errors__item { position: relative; padding-block: 0.2rem; padding-inline-start: 0.875rem; font-size: 0.8125rem; line-height: 1.55; color: #be123c; }
            .ps-form-errors__item::before { content: ''; position: absolute; inset-inline-start: 0; top: 0.65em; width: 0.25rem; height: 0.25rem; border-radius: 9999px; background: #e11d48; opacity: 0.45; }
        </style>
    @endif
    @stack('head')
</head>
<body class="flex min-h-screen overflow-x-hidden flex-col font-sans antialiased bg-gray-50">
    @include('layouts.partials.header')

    <main class="min-w-0 flex-1 overflow-x-clip py-8 sm:py-10">
        <div class="ps-container min-w-0">
            @yield('content')
        </div>

        @stack('full-bleed')

        @hasSection('content-tail')
            <div class="ps-container min-w-0">
                @yield('content-tail')
            </div>
        @endif
    </main>

    @include('layouts.partials.footer')

    <x-ui.floating-call-button />

    @stack('overlays')
    @stack('scripts')
</body>
</html>
