@props([
    'groups' => collect(),
    'emptyMessage' => 'مدلی با این فیلتر یافت نشد.',
    'selectable' => false,
    'selectedModelSlug' => null,
    'label' => null,
    'fieldId' => 'model-filter',
])

@php
    $chipClass = 'model-chip inline-flex max-w-full items-center rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-medium text-ink transition hover:border-brand/40 hover:bg-brand-soft/50 sm:px-3 sm:py-1.5 sm:text-sm';
    $chipSelectedClass = 'border-brand bg-brand-soft text-brand shadow-sm';
@endphp

@if ($groups->isEmpty())
    <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
        <p class="text-sm text-ink-muted">{{ $emptyMessage }}</p>
    </div>
@else
    <div data-model-category-list>
        @if ($label)
            <p class="mb-2 text-sm font-medium text-ink">{{ $label }}</p>
        @endif

        @if ($selectable)
            <input
                type="hidden"
                id="{{ $fieldId }}"
                data-catalog-field="model"
                value="{{ $selectedModelSlug ?? '' }}"
            >

            <div class="mb-3 flex flex-wrap gap-1.5" role="listbox" aria-label="{{ $label ?? 'انتخاب مدل' }}">
                <button
                    type="button"
                    data-model-pick
                    data-model-value=""
                    @class([
                        $chipClass,
                        $chipSelectedClass => blank($selectedModelSlug),
                    ])
                >
                    همه مدل‌ها
                </button>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($groups as $group)
                <section class="rounded-xl border border-line bg-surface/40 p-3">
                    <h3 class="mb-2 text-xs font-bold text-brand">{{ $group['label'] }}</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($group['models'] as $entry)
                            @if ($selectable)
                                <button
                                    type="button"
                                    data-model-pick
                                    data-model-value="{{ $entry['model']->slug }}"
                                    @class([
                                        $chipClass,
                                        $chipSelectedClass => $selectedModelSlug === $entry['model']->slug,
                                    ])
                                >
                                    {{ $entry['label'] }}
                                </button>
                            @else
                                <a href="{{ $entry['url'] }}" class="{{ $chipClass }}">
                                    {{ $entry['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    @if ($selectable)
        @once
            @push('scripts')
                <script>
                    (function () {
                        const selectedClasses = ['border-brand', 'bg-brand-soft', 'text-brand', 'shadow-sm'];

                        const setSelectedOption = function (list, value) {
                            const hidden = list.querySelector('[data-catalog-field="model"]');

                            if (hidden) {
                                hidden.value = value;
                            }

                            list.querySelectorAll('[data-model-pick]').forEach(function (button) {
                                const isSelected = button.dataset.modelValue === value;

                                selectedClasses.forEach(function (className) {
                                    button.classList.toggle(className, isSelected);
                                });
                            });
                        };

                        document.querySelectorAll('[data-model-category-list]').forEach(function (list) {
                            list.querySelectorAll('[data-model-pick]').forEach(function (button) {
                                button.addEventListener('click', function () {
                                    setSelectedOption(list, button.dataset.modelValue || '');
                                });
                            });
                        });
                    })();
                </script>
            @endpush
        @endonce
    @endif
@endif
