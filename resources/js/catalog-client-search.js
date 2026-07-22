export default function catalogClientSearch({
    itemSelector,
    sectionSelector = null,
    textAttribute = 'searchText',
    minChars = 2,
    debounceMs = 0,
    gridSelector = null,
    loadMoreWrapSelector = null,
    loadMoreButtonSelector = null,
    initialRows = null,
} = {}) {
    return {
        query: '',
        emptyVisible: false,
        timer: null,
        items: [],
        sections: [],
        itemSelector,
        sectionSelector,
        textAttribute,
        minChars,
        debounceMs,
        gridSelector,
        loadMoreWrapSelector,
        loadMoreButtonSelector,
        initialRows,
        expanded: false,

        init() {
            this.refreshItems();
            this.applyFilter('');

            if (this.loadMoreButtonSelector) {
                const button = document.querySelector(this.loadMoreButtonSelector);

                button?.addEventListener('click', () => {
                    this.expanded = true;
                    this.applyFilter(this.query);
                });
            }

            if (this.initialRows) {
                window.addEventListener('resize', () => {
                    if (! this.expanded && this.query.trim() === '') {
                        this.applyFilter('');
                    }
                });
            }
        },

        refreshItems() {
            this.items = Array.from(document.querySelectorAll(this.itemSelector));
            this.sections = this.sectionSelector
                ? Array.from(document.querySelectorAll(this.sectionSelector))
                : [];
        },

        getInitialLimit() {
            if (! this.initialRows || ! this.gridSelector) {
                return null;
            }

            const grid = document.querySelector(this.gridSelector);

            if (! grid) {
                return this.initialRows;
            }

            const columns = window.getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean);

            return (columns.length || 1) * this.initialRows;
        },

        onQueryInput() {
            this.scheduleSearch({ force: false });
        },

        scheduleSearch({ force = false } = {}) {
            window.clearTimeout(this.timer);

            const value = this.query.trim();

            if (value === '') {
                this.applyFilter('');

                return;
            }

            if (! force && value.length < this.minChars) {
                return;
            }

            this.timer = window.setTimeout(() => {
                this.applyFilter(value);
            }, force ? 0 : this.debounceMs);
        },

        applyFilter(rawQuery) {
            const query = rawQuery.trim().toLowerCase();
            const isSearching = query !== '';
            const initialLimit = this.getInitialLimit();
            let visibleCount = 0;

            this.items.forEach((item, index) => {
                const text = (item.dataset[this.textAttribute] || item.textContent || '').toLowerCase();
                const matches = query === '' || text.includes(query);
                const withinInitialLimit = this.expanded || isSearching || initialLimit === null || index < initialLimit;
                const visible = matches && withinInitialLimit;

                item.classList.toggle('hidden', !visible);

                if (visible) {
                    visibleCount += 1;
                }
            });

            this.sections.forEach((section) => {
                const hasVisibleChild = Array.from(section.querySelectorAll(this.itemSelector))
                    .some((item) => !item.classList.contains('hidden'));

                section.classList.toggle('hidden', query !== '' && !hasVisibleChild);
            });

            this.emptyVisible = query !== '' && visibleCount === 0;

            if (this.loadMoreWrapSelector) {
                const wrap = document.querySelector(this.loadMoreWrapSelector);
                const showLoadMore = ! this.expanded
                    && ! isSearching
                    && initialLimit !== null
                    && this.items.length > initialLimit;

                wrap?.classList.toggle('hidden', !showLoadMore);
                wrap?.classList.toggle('flex', showLoadMore);
            }
        },

        clearSearch() {
            window.clearTimeout(this.timer);
            this.query = '';
            this.applyFilter('');
            this.$refs.searchInput?.focus();
        },
    };
}
