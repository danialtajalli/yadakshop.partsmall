export default function catalogRemoteSearch({
    action,
    resultsSelector = '[data-catalog-search-results]',
    minChars = 2,
    debounceMs = 1000,
    initialQuery = '',
    initialStateId = '',
    initialCityId = '',
    initialSpecializationId = '',
    csrf = '',
} = {}) {
    return {
        query: initialQuery ?? '',
        loading: false,
        timer: null,
        action,
        resultsSelector,
        minChars,
        debounceMs,
        csrf,
        controller: null,
        clearing: false,
        page: 1,
        stateId: initialStateId != null && initialStateId !== '' ? String(initialStateId) : '',
        cityId: initialCityId != null && initialCityId !== '' ? String(initialCityId) : '',
        specializationId: initialSpecializationId != null && initialSpecializationId !== ''
            ? String(initialSpecializationId)
            : '',

        init() {
            this.$nextTick(() => {
                this.captureFilters();
                this.bindFilterTriggers();
                this.bindPagination();
            });
        },

        formEl() {
            if (this.$el instanceof HTMLFormElement) {
                return this.$el;
            }

            return this.$el.querySelector('form')
                || document.getElementById('listing-filters-form');
        },

        selectValue(select) {
            if (! (select instanceof HTMLSelectElement)) {
                return '';
            }

            let value = '';

            if (window.jQuery) {
                const $select = window.jQuery(select);

                if ($select.hasClass('select2-hidden-accessible')) {
                    const selected = $select.val();
                    value = Array.isArray(selected) ? (selected[0] ?? '') : (selected ?? '');
                }
            }

            if (value === '' || value == null) {
                value = select.value || select.selectedOptions?.[0]?.value || '';
            }

            return String(value ?? '');
        },

        captureFilters() {
            const form = this.formEl();
            const stateSelect = form?.querySelector('[data-listing-state]')
                || document.getElementById('listing-state');
            const citySelect = form?.querySelector('[data-listing-city]')
                || document.getElementById('listing-city');
            const specializationSelect = form?.querySelector('[name="specialization_id"]')
                || document.getElementById('listing-specialization');

            this.stateId = this.selectValue(stateSelect);
            this.cityId = this.selectValue(citySelect);
            this.specializationId = this.selectValue(specializationSelect);
        },

        afterFilterDomUpdate(callback) {
            // Let Select2 / location-selects finish updating options, then read values.
            window.setTimeout(() => {
                this.captureFilters();
                callback();
            }, 0);
        },

        bindFilterTriggers() {
            const form = this.formEl();

            if (! (form instanceof HTMLFormElement)) {
                return;
            }

            const stateSelect = form.querySelector('[data-listing-state]');
            const citySelect = form.querySelector('[data-listing-city]');
            const specializationSelect = form.querySelector('[name="specialization_id"]');

            const onStateChange = () => {
                if (this.clearing) {
                    return;
                }

                this.afterFilterDomUpdate(() => {
                    if (this.cityId !== '') {
                        this.scheduleSearch({ force: true });
                    } else {
                        this.scheduleSearch({ force: false, bypassQueryGate: true });
                    }
                });
            };

            const onCityChange = () => {
                if (this.clearing) {
                    return;
                }

                this.afterFilterDomUpdate(() => {
                    if (this.cityId !== '') {
                        this.scheduleSearch({ force: true });
                    } else if (this.stateId !== '') {
                        this.scheduleSearch({ force: false, bypassQueryGate: true });
                    } else {
                        this.scheduleSearch({ force: true });
                    }
                });
            };

            const onSpecializationChange = () => {
                if (this.clearing) {
                    return;
                }

                this.afterFilterDomUpdate(() => {
                    this.scheduleSearch({ force: true });
                });
            };

            stateSelect?.addEventListener('change', onStateChange);
            citySelect?.addEventListener('change', onCityChange);
            specializationSelect?.addEventListener('change', onSpecializationChange);

            if (window.jQuery) {
                if (stateSelect) {
                    window.jQuery(stateSelect)
                        .off('.catalogRemoteSearch')
                        .on('select2:select.catalogRemoteSearch select2:clear.catalogRemoteSearch change.catalogRemoteSearch', onStateChange);
                }

                if (citySelect) {
                    window.jQuery(citySelect)
                        .off('.catalogRemoteSearch')
                        .on('select2:select.catalogRemoteSearch select2:clear.catalogRemoteSearch change.catalogRemoteSearch', onCityChange);
                }

                if (specializationSelect) {
                    window.jQuery(specializationSelect)
                        .off('.catalogRemoteSearch')
                        .on('select2:select.catalogRemoteSearch select2:clear.catalogRemoteSearch change.catalogRemoteSearch', onSpecializationChange);
                }
            }
        },

        bindPagination() {
            this.$el.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');

                if (! (link instanceof HTMLAnchorElement) || ! this.$el.contains(link)) {
                    return;
                }

                const results = this.$el.querySelector(this.resultsSelector);

                if (! results?.contains(link) || ! link.closest('nav')) {
                    return;
                }

                event.preventDefault();

                let page = 1;

                try {
                    const url = new URL(link.href, window.location.origin);
                    page = Number(url.searchParams.get('page') || 1);
                } catch {
                    page = 1;
                }

                if (! Number.isFinite(page) || page < 1) {
                    page = 1;
                }

                this.runSearch({ page });
            });
        },

        onQueryInput() {
            this.scheduleSearch({ force: false });
        },

        scheduleSearch({ force = false, bypassQueryGate = false } = {}) {
            window.clearTimeout(this.timer);

            if (force) {
                this.runSearch();

                return;
            }

            if (! bypassQueryGate) {
                const value = this.query.trim();

                if (value === '') {
                    this.runSearch();

                    return;
                }

                if (value.length < this.minChars) {
                    return;
                }
            }

            this.timer = window.setTimeout(() => {
                this.runSearch();
            }, this.debounceMs);
        },

        buildFormData() {
            // Use Alpine-tracked filter ids (set on select change / init).
            // Re-reading Select2 on every query search was dropping state/city.
            const formData = new FormData();
            const token = this.csrf
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || '';

            if (token !== '') {
                formData.set('_token', token);
            }

            if (this.stateId !== '') {
                formData.set('state_id', this.stateId);
            }

            if (this.cityId !== '') {
                formData.set('city_id', this.cityId);
            }

            if (this.specializationId !== '') {
                formData.set('specialization_id', this.specializationId);
            }

            const q = this.query.trim();

            if (q !== '') {
                formData.set('q', q);
            }

            if (this.page > 1) {
                formData.set('page', String(this.page));
            }

            return formData;
        },

        async runSearch({ page = 1 } = {}) {
            if (! this.action) {
                return;
            }

            this.page = page > 1 ? page : 1;
            this.controller?.abort();
            this.controller = new AbortController();
            this.loading = true;

            try {
                const formData = this.buildFormData();
                const token = formData.get('_token')
                    || this.csrf
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '';

                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                        'X-CSRF-TOKEN': String(token),
                    },
                    body: formData,
                    signal: this.controller.signal,
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error(`Search failed (${response.status})`);
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextResults = doc.querySelector(this.resultsSelector);
                const currentResults = this.$el.querySelector(this.resultsSelector)
                    || document.querySelector(this.resultsSelector);

                if (nextResults && currentResults) {
                    currentResults.innerHTML = nextResults.innerHTML;
                }
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        clearSearch() {
            window.clearTimeout(this.timer);
            this.clearing = true;
            this.query = '';
            this.page = 1;
            this.stateId = '';
            this.cityId = '';
            this.specializationId = '';

            const form = this.formEl();

            if (form instanceof HTMLFormElement) {
                form.querySelectorAll('select').forEach((select) => {
                    select.value = '';

                    if (window.jQuery?.(select).hasClass('select2-hidden-accessible')) {
                        window.jQuery(select).val('').trigger('change');
                    } else {
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }

            this.clearing = false;
            this.runSearch();
            this.$refs.searchInput?.focus();
        },
    };
}
