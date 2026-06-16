@props([
    'action',
    'filters' => [],
    'states' => collect(),
    'cities' => collect(),
    'citiesByState' => [],
    'specializations' => collect(),
    'showSpecializationFilter' => false,
])

<form method="GET" action="{{ $action }}" id="listing-filters-form">
    <x-catalog.search-bar
        id="listing-search"
        name="q"
        :value="$filters['q'] ?? ''"
        placeholder="جستجو در نام، آدرس و..."
        :clear-url="$action"
        class="mb-0"
    >
        <x-slot:between>
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
        </x-slot:between>
    </x-catalog.search-bar>
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
