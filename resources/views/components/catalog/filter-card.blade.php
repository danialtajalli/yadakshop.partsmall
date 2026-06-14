@props(['clearUrl' => null])

<div {{ $attributes->merge(['class' => 'ps-card mb-6 p-5 sm:p-6']) }} data-catalog-filter>
    {{ $slot }}

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="button" data-catalog-apply class="ps-btn-primary">اعمال</button>
        @if ($clearUrl)
            <a href="{{ $clearUrl }}" class="ps-btn-secondary">پاک کردن</a>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const buildPathUrl = function (template, values) {
                    let url = template;

                    Object.entries(values).forEach(function ([key, value]) {
                        url = url.replace('__' + key.toUpperCase() + '__', encodeURIComponent(value));
                    });

                    return url.replace(/\/__[A-Z_]+__(?=\/|$)/g, '').replace(/__([A-Z_]+)__/g, '');
                };

                const buildPartsUrl = function (card) {
                    const company = card.querySelector('[data-catalog-field="company"]')?.value || '';
                    const car = card.querySelector('[data-catalog-field="car"]')?.value || '';
                    const model = card.querySelector('[data-catalog-field="model"]')?.value || '';
                    const query = new URLSearchParams();

                    if (company && car && model) {
                        let url = buildPathUrl(card.dataset.catalogPartsVehicleTemplate, {
                            company: company,
                            car: car,
                            model: model,
                        });

                        card.querySelectorAll('[data-catalog-query-field]').forEach(function (field) {
                            if (field.value) {
                                query.set(field.name, field.value);
                            }
                        });

                        const queryString = query.toString();

                        return queryString ? url + '?' + queryString : url;
                    }

                    if (company) {
                        query.set('company', company);
                    }

                    if (car) {
                        query.set('car', car);
                    }

                    if (model) {
                        query.set('model', model);
                    }

                    card.querySelectorAll('[data-catalog-query-field]').forEach(function (field) {
                        if (field.value) {
                            query.set(field.name, field.value);
                        }
                    });

                    const base = card.dataset.catalogPartsBase || '';
                    const queryString = query.toString();

                    return queryString ? base + '?' + queryString : base;
                };

                const navigateFromCard = function (card) {
                    const type = card.dataset.catalogType;

                    if (type === 'cars') {
                        const company = card.querySelector('[data-catalog-field="company"]')?.value || '';

                        window.location.href = company
                            ? buildPathUrl(card.dataset.catalogCarsCompanyTemplate, { company: company })
                            : card.dataset.catalogCarsBase;
                        return;
                    }

                    if (type === 'models') {
                        const company = card.querySelector('[data-catalog-field="company"]')?.value || '';
                        const car = card.querySelector('[data-catalog-field="car"]')?.value || '';

                        if (company && car) {
                            window.location.href = buildPathUrl(card.dataset.catalogModelsCarTemplate, {
                                company: company,
                                car: car,
                            });
                            return;
                        }

                        if (company) {
                            window.location.href = buildPathUrl(card.dataset.catalogModelsCompanyTemplate, {
                                company: company,
                            });
                            return;
                        }

                        window.location.href = card.dataset.catalogModelsBase;
                        return;
                    }

                    if (type === 'parts') {
                        window.location.href = buildPartsUrl(card);
                    }
                };

                document.querySelectorAll('[data-catalog-filter]').forEach(function (card) {
                    card.querySelector('[data-catalog-apply]')?.addEventListener('click', function () {
                        navigateFromCard(card);
                    });
                });
            })();
        </script>
    @endpush
@endonce
