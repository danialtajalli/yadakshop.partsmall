@props([
    'groups' => collect(),
    'emptyMessage' => 'مدلی با این فیلتر یافت نشد.',
])

@if ($groups->isEmpty())
    <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
        <p class="text-sm text-ink-muted">{{ $emptyMessage }}</p>
    </div>
@else
    <div data-model-category-explorer>
        <div data-model-category-step="categories">
            <p class="mb-4 text-sm text-ink-muted">ابتدا دسته مدل را انتخاب کنید.</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($groups as $group)
                    <button
                        type="button"
                        data-model-category-trigger="{{ $group['slug'] }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-medium text-ink shadow-card transition hover:border-brand/30 hover:bg-brand-soft hover:text-brand"
                    >
                        <span>{{ $group['label'] }}</span>
                        <span class="rounded-full bg-surface px-2 py-0.5 text-xs text-ink-muted">{{ $group['models']->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        @foreach ($groups as $group)
            <div
                data-model-category-step="models"
                data-model-category-panel="{{ $group['slug'] }}"
                class="hidden"
            >
                <button
                    type="button"
                    data-model-category-back
                    class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand transition hover:text-brand-dark"
                >
                    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    بازگشت به دسته‌ها
                </button>
                <p class="mb-4 text-sm text-ink-muted">مدل‌های دسته «{{ $group['label'] }}»:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($group['models'] as $entry)
                        <a
                            href="{{ $entry['url'] }}"
                            class="rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-medium text-ink shadow-card transition hover:border-brand/30 hover:bg-brand-soft hover:text-brand"
                        >
                            {{ $entry['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    document.querySelectorAll('[data-model-category-explorer]').forEach(function (explorer) {
                        const categoriesStep = explorer.querySelector('[data-model-category-step="categories"]');
                        const modelPanels = explorer.querySelectorAll('[data-model-category-step="models"]');

                        const showCategories = function () {
                            categoriesStep?.classList.remove('hidden');
                            modelPanels.forEach(function (panel) {
                                panel.classList.add('hidden');
                            });
                        };

                        explorer.querySelectorAll('[data-model-category-trigger]').forEach(function (button) {
                            button.addEventListener('click', function () {
                                const slug = button.dataset.modelCategoryTrigger;

                                categoriesStep?.classList.add('hidden');
                                modelPanels.forEach(function (panel) {
                                    panel.classList.toggle('hidden', panel.dataset.modelCategoryPanel !== slug);
                                });
                            });
                        });

                        explorer.querySelectorAll('[data-model-category-back]').forEach(function (button) {
                            button.addEventListener('click', showCategories);
                        });
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
