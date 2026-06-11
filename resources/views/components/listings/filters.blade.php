@props([
    'action',
    'filters' => [],
    'states' => collect(),
    'cities' => collect(),
    'citiesByState' => [],
    'specializations' => collect(),
    'showSpecializationFilter' => false,
])

<form
    method="GET"
    action="{{ $action }}"
    class="ps-card mb-8 space-y-4 p-5 sm:p-6"
>
    <div class="relative">
        <label for="listing-search" class="sr-only">جستجو</label>
        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
            id="listing-search"
            type="search"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            placeholder="جستجو در نام، آدرس و..."
            class="w-full rounded-xl border border-line bg-white py-3 pe-4 ps-12 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
        >
    </div>

    <div @class([
        'grid gap-4',
        'sm:grid-cols-2 lg:grid-cols-3' => $showSpecializationFilter,
        'sm:grid-cols-2' => ! $showSpecializationFilter,
    ])>
        <div>
            <label for="listing-state" class="mb-1.5 block text-xs font-medium text-ink-muted">استان</label>
            <select
                id="listing-state"
                name="state_id"
                data-listing-state
                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
                <option value="">همه استان‌ها</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}" @selected(($filters['state_id'] ?? null) == $state->id)>
                        {{ $state->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="listing-city" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
            <select
                id="listing-city"
                name="city_id"
                data-listing-city
                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                @disabled(! ($filters['state_id'] ?? null))
            >
                <option value="">همه شهرها</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(($filters['city_id'] ?? null) == $city->id)>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($showSpecializationFilter)
            <div>
                <label for="listing-specialization" class="mb-1.5 block text-xs font-medium text-ink-muted">تخصص‌ها</label>
                <select
                    id="listing-specialization"
                    name="specialization_id"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
                    <option value="">همه تخصص‌ها</option>
                    @foreach ($specializations as $specialization)
                        <option value="{{ $specialization->id }}" @selected(($filters['specialization_id'] ?? null) == $specialization->id)>
                            {{ $specialization->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="ps-btn-primary">اعمال فیلتر</button>
        <a href="{{ $action }}" class="ps-btn-secondary">پاک کردن</a>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                const citiesByState = @json($citiesByState);

                document.querySelectorAll('[data-listing-state]').forEach(function (stateSelect) {
                    const form = stateSelect.closest('form');
                    const citySelect = form?.querySelector('[data-listing-city]');

                    if (!citySelect) {
                        return;
                    }

                    const selectedCityId = citySelect.value;

                    stateSelect.addEventListener('change', function () {
                        const stateId = this.value;
                        const cities = citiesByState[stateId] || [];

                        citySelect.innerHTML = '<option value="">همه شهرها</option>';

                        cities.forEach(function (city) {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;

                            if (String(city.id) === String(selectedCityId)) {
                                option.selected = true;
                            }

                            citySelect.appendChild(option);
                        });

                        citySelect.disabled = !stateId;
                    });
                });
            })();
        </script>
    @endpush
@endonce
