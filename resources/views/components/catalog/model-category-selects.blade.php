@props([
    'groups' => collect(),
    'selectedModelSlug' => null,
    'categoryFieldId' => 'model-category-filter',
    'modelFieldId' => 'model-filter',
    'categoryLabel' => 'دسته مدل',
    'modelLabel' => 'مدل',
])

<div class="grid gap-4 sm:grid-cols-2" data-model-category-selects>
    <div>
        <label for="{{ $categoryFieldId }}" class="mb-2 block text-sm font-medium text-ink">{{ $categoryLabel }}</label>
        <select
            id="{{ $categoryFieldId }}"
            data-catalog-field="model_category"
            data-model-category-select
            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
        >
            <option value="">انتخاب دسته مدل</option>
            @foreach ($groups as $group)
                <option value="{{ $group['slug'] }}">{{ $group['label'] }} ({{ $group['models']->count() }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="{{ $modelFieldId }}" class="mb-2 block text-sm font-medium text-ink">{{ $modelLabel }}</label>
        <select
            id="{{ $modelFieldId }}"
            data-catalog-field="model"
            data-model-select
            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20 disabled:cursor-not-allowed disabled:bg-surface disabled:text-ink-muted"
            disabled
        >
            <option value="">ابتدا دسته مدل را انتخاب کنید</option>
            @foreach ($groups as $group)
                @foreach ($group['models'] as $entry)
                    <option
                        value="{{ $entry['model']->slug }}"
                        data-model-category="{{ $group['slug'] }}"
                        @selected($selectedModelSlug === $entry['model']->slug)
                    >
                        {{ $entry['label'] }}
                    </option>
                @endforeach
            @endforeach
        </select>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const syncModelSelect = function (wrapper) {
                    const categorySelect = wrapper.querySelector('[data-model-category-select]');
                    const modelSelect = wrapper.querySelector('[data-model-select]');

                    if (!categorySelect || !modelSelect) {
                        return;
                    }

                    const selectedCategory = categorySelect.value;
                    const options = Array.from(modelSelect.options);
                    let visibleCount = 0;

                    options.forEach(function (option, index) {
                        if (index === 0) {
                            option.hidden = selectedCategory !== '';
                            option.disabled = selectedCategory === '';
                            option.textContent = selectedCategory === ''
                                ? 'ابتدا دسته مدل را انتخاب کنید'
                                : 'انتخاب مدل';
                            return;
                        }

                        const matches = option.dataset.modelCategory === selectedCategory;
                        option.hidden = !matches;
                        option.disabled = !matches;

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    modelSelect.disabled = selectedCategory === '';

                    if (selectedCategory !== '' && !Array.from(modelSelect.options).some(function (option) {
                        return !option.disabled && option.value === modelSelect.value;
                    })) {
                        modelSelect.value = '';
                    }

                    if (selectedCategory !== '' && visibleCount === 0) {
                        modelSelect.value = '';
                    }
                };

                const initializeWrapper = function (wrapper) {
                    const categorySelect = wrapper.querySelector('[data-model-category-select]');
                    const modelSelect = wrapper.querySelector('[data-model-select]');

                    if (!categorySelect || !modelSelect) {
                        return;
                    }

                    const selectedModel = modelSelect.value;

                    if (selectedModel) {
                        const selectedOption = Array.from(modelSelect.options).find(function (option) {
                            return option.value === selectedModel;
                        });

                        if (selectedOption?.dataset.modelCategory) {
                            categorySelect.value = selectedOption.dataset.modelCategory;
                        }
                    }

                    syncModelSelect(wrapper);

                    categorySelect.addEventListener('change', function () {
                        modelSelect.value = '';
                        syncModelSelect(wrapper);
                    });
                };

                document.querySelectorAll('[data-model-category-selects]').forEach(initializeWrapper);
            })();
        </script>
    @endpush
@endonce
