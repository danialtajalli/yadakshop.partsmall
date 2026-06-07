@props(['id'])

<div {{ $attributes->merge(['class' => 'ps-expandable relative']) }} data-expandable id="{{ $id }}">
    <div class="ps-expandable-content ps-prose" data-expandable-content>
        {{ $slot }}
    </div>
    <button
        type="button"
        class="ps-expandable-toggle"
        data-expandable-toggle
        aria-expanded="false"
        aria-controls="{{ $id }}-content"
        hidden
    >
        <span data-expandable-label-more>ادامه مطلب</span>
        <span data-expandable-label-less hidden>بستن</span>
        <svg data-expandable-icon class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
</div>

@once
    @push('scripts')
        <script>
            function initExpandableDescriptions(root = document) {
                root.querySelectorAll('[data-expandable]').forEach((block) => {
                    if (block.dataset.expandableReady) return;
                    block.dataset.expandableReady = '1';

                    const content = block.querySelector('[data-expandable-content]');
                    const toggle = block.querySelector('[data-expandable-toggle]');
                    const icon = block.querySelector('[data-expandable-icon]');
                    const labelMore = block.querySelector('[data-expandable-label-more]');
                    const labelLess = block.querySelector('[data-expandable-label-less]');

                    const updateToggle = () => {
                        if (!content || !toggle) return;

                        if (block.classList.contains('is-expanded')) {
                            toggle.hidden = false;
                            return;
                        }

                        block.classList.add('is-expanded');
                        const fullHeight = content.scrollHeight;
                        block.classList.remove('is-expanded');
                        toggle.hidden = fullHeight <= content.clientHeight + 2;
                    };

                    toggle?.addEventListener('click', () => {
                        const expanded = block.classList.toggle('is-expanded');
                        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                        icon?.classList.toggle('rotate-180', expanded);
                        labelMore?.toggleAttribute('hidden', expanded);
                        labelLess?.toggleAttribute('hidden', !expanded);
                        updateToggle();
                    });

                    updateToggle();
                    window.addEventListener('resize', updateToggle);
                });
            }

            document.addEventListener('DOMContentLoaded', () => initExpandableDescriptions());
            document.querySelectorAll('dialog').forEach((dialog) => {
                dialog.addEventListener('toggle', () => {
                    if (dialog.open) {
                        initExpandableDescriptions(dialog);
                    }
                });
            });
        </script>
    @endpush
@endonce
