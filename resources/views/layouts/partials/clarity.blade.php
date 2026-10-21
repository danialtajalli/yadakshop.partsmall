@php($clarityProjectId = trim((string) config('services.clarity.project_id')))
@if (app()->environment('production')
    && config('services.clarity.enabled')
    && preg_match('/\A[a-zA-Z0-9]+\z/', $clarityProjectId)
    && ! request()->is('admin', 'admin/*'))
    @once
        <script defer src="{{ asset('js/clarity.js') }}?v=3" data-clarity-project-id="{{ $clarityProjectId }}"></script>
    @endonce
@endif
