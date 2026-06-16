@props([
    'formId',
    'inputId',
    'emptyId' => null,
    'itemSelector',
    'sectionSelector' => null,
    'textAttribute' => 'searchText',
    'showClear' => true,
])

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById(@json($formId));
            const searchInput = document.getElementById(@json($inputId));
            const emptyMessage = @json($emptyId) ? document.getElementById(@json($emptyId)) : null;
            const itemSelector = @json($itemSelector);
            const sectionSelector = @json($sectionSelector);
            const textAttribute = @json($textAttribute);
            const showClear = @json($showClear);

            if (!form || !searchInput) {
                return;
            }

            const items = Array.from(document.querySelectorAll(itemSelector));
            const sections = sectionSelector
                ? Array.from(document.querySelectorAll(sectionSelector))
                : [];

            const filterItems = function () {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                items.forEach(function (item) {
                    const text = (item.dataset[textAttribute] || item.textContent || '').toLowerCase();
                    const matches = query === '' || text.includes(query);

                    item.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (sections.length > 0) {
                    sections.forEach(function (section) {
                        const hasVisibleChild = Array.from(section.querySelectorAll(itemSelector))
                            .some(function (item) {
                                return !item.classList.contains('hidden');
                            });

                        section.classList.toggle('hidden', query !== '' && !hasVisibleChild);
                    });
                }

                if (emptyMessage) {
                    emptyMessage.classList.toggle('hidden', query === '' || visibleCount > 0);
                }
            };

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                filterItems();
            });

            if (showClear) {
                const clearButton = form.querySelector('[data-catalog-search-clear]');

                if (clearButton) {
                    clearButton.addEventListener('click', function () {
                        searchInput.value = '';
                        filterItems();
                        searchInput.focus();
                    });
                }
            }
        })();
    </script>
@endpush
