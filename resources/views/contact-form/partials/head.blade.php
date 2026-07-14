<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'فرم تماس' }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

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
                    ink: {
                        DEFAULT: '#0f172a',
                        muted: '#64748b',
                    },
                    surface: '#f1f5f9',
                    line: '#e2e8f0',
                },
            },
        },
    };
</script>

<style>
    html, body {
        margin: 0;
        padding: 0;
        background: transparent;
        font-family: Vazirmatn, 'Segoe UI', Tahoma, ui-sans-serif, system-ui, sans-serif;
        color: #0f172a;
        direction: rtl;
    }

    .didar-contact-form__input--error {
        border-color: #f87171 !important;
        background-color: #fffafa !important;
    }

    .didar-contact-form__input--error:focus {
        border-color: #ef4444 !important;
        --tw-ring-color: rgb(239 68 68 / 0.25) !important;
    }
</style>
