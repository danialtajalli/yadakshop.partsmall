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
    method="POST"
    action="{{ $action }}"
    id="listing-filters-form"
    data-cities-by-state='@json($citiesByState)'
    data-no-progress
    @submit.prevent="scheduleSearch({ force: true })"
>
    @csrf
    <x-catalog.search-bar
        id="listing-search"
        name="q"
        :value="$filters['q'] ?? ''"
        placeholder="جستجو در نام، آدرس و..."
        alpine
        class="mb-0"
    >
        <x-slot:between>
            <div @class([
                'grid grid-cols-2 gap-3 sm:gap-4',
                'lg:grid-cols-3' => $showSpecializationFilter,
            ])>
                <div class="min-w-0">
                    <label for="listing-state" class="mb-1.5 block text-xs font-medium text-ink-muted">استان</label>
                    <div class="ps-searchable-select">
                        <select
                            id="listing-state"
                            name="state_id"
                            data-listing-state
                            data-searchable-select
                        >
                            <option value="">همه استان‌ها</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected(($filters['state_id'] ?? null) == $state->id)>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="min-w-0">
                    <label for="listing-city" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
                    <div class="ps-searchable-select">
                        <select
                            id="listing-city"
                            name="city_id"
                            data-listing-city
                            data-searchable-select
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
                </div>

                @if ($showSpecializationFilter)
                    <div class="col-span-2 min-w-0 lg:col-span-1">
                        <label for="listing-specialization" class="mb-1.5 block text-xs font-medium text-ink-muted">تخصص‌ها</label>
                        <div class="ps-searchable-select">
                            <select
                                id="listing-specialization"
                                name="specialization_id"
                                data-listing-specialization
                                data-searchable-select
                            >
                                <option value="" data-all-option>همه تخصص‌ها</option>
                                @foreach ($specializations as $specialization)
                                    <option value="{{ $specialization->id }}" @selected(($filters['specialization_id'] ?? null) == $specialization->id)>
                                        {{ $specialization->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot:between>
    </x-catalog.search-bar>
</form>
