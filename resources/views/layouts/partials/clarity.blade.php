@php($clarityProjectId = trim((string) config('services.clarity.project_id')))
@if (app()->environment('production')
    && config('services.clarity.enabled')
    && preg_match('/\A[a-zA-Z0-9]+\z/', $clarityProjectId)
    && ! request()->is('admin', 'admin/*'))
    @once
        <link rel="stylesheet" href="{{ asset('css/clarity.css') }}?v=2">
        <script defer src="{{ asset('js/clarity.js') }}?v=2" data-clarity-project-id="{{ $clarityProjectId }}"></script>

        @push('overlays')
            <aside id="ps-clarity-consent" class="ps-clarity-consent" dir="rtl" lang="fa" aria-labelledby="ps-clarity-title" hidden>
                <p id="ps-clarity-title">با اجازه شما، از Microsoft Clarity برای تحلیل و بازپخش بازدیدها استفاده می‌کنیم. <a href="https://privacy.microsoft.com/privacystatement" target="_blank" rel="noopener noreferrer">اطلاعات بیشتر</a></p>
                <div class="ps-clarity-actions">
                    <button type="button" id="ps-clarity-accept">می‌پذیرم</button>
                    <button type="button" id="ps-clarity-reject">رد کردن</button>
                </div>
            </aside>
        @endpush
        @push('privacy-settings')
            <button type="button" id="ps-clarity-settings" class="ps-clarity-settings" dir="rtl" lang="fa" aria-controls="ps-clarity-consent" aria-expanded="false" hidden>تنظیمات حریم خصوصی</button>
        @endpush
    @endonce
@endif
