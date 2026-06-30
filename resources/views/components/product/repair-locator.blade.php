@props(['repairLocator'])

@php
    $modalId = 'product-repair-locator-modal-'.$repairLocator['category']->id;
    $stateId = 'repair-locator-state-'.$repairLocator['category']->id;
    $cityId = 'repair-locator-city-'.$repairLocator['category']->id;
@endphp

<article>
    <button
        type="button"
        class="group flex w-full items-center justify-between gap-3 rounded-2xl border border-line bg-white px-3.5 py-3 text-start text-sm shadow-sm transition hover:border-brand/30 hover:bg-brand-soft/30 hover:shadow-card"
        onclick="document.getElementById('{{ $modalId }}').showModal()"
    >
        <span class="min-w-0">
            <span class="block truncate font-bold text-ink">{{ $repairLocator['category']->name }}</span>
        </span>
        <span class="shrink-0 rounded-full bg-brand px-3 py-1.5 text-xs font-bold text-white transition group-hover:bg-brand-dark">
            انتخاب محدوده
        </span>
    </button>
</article>

<dialog
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-2xl border border-line bg-white p-0 shadow-2xl backdrop:bg-ink/40 open:animate-none"
>
    <div class="border-b border-line bg-linear-to-l from-gray-100 via-white px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-brand">انتخاب محدوده خدمات</p>
                <h2 class="mt-1 text-base font-bold text-ink">تعمیرگاه‌های {{ $repairLocator['category']->name }}</h2>
            </div>
            <button
                type="button"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg text-ink-muted transition hover:bg-surface hover:text-ink"
                onclick="document.getElementById('{{ $modalId }}').close()"
                aria-label="بستن"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <form
        method="GET"
        action="{{ route('repair-shops.index') }}"
        class="space-y-4 px-5 py-5"
        data-repair-locator-form
    >
        <input type="hidden" name="specialization_id" value="{{ $repairLocator['category']->id }}">

        <p class="rounded-xl bg-surface px-4 py-3 text-sm leading-7 text-ink-muted">
            استان و شهر خود را انتخاب کنید تا تعمیرگاه‌های مرتبط با
            <span class="font-medium text-ink">{{ $repairLocator['category']->name }}</span>
            برای
            <span class="font-medium text-ink">{{ $repairLocator['carName'] }}</span>
            را ببینید.
        </p>

        <div>
            <label for="{{ $stateId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">استان</label>
            <select
                id="{{ $stateId }}"
                name="state_id"
                required
                data-repair-locator-state
                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
                <option value="">انتخاب استان</option>
                @foreach ($repairLocator['states'] as $state)
                    <option value="{{ $state->id }}" @selected(($repairLocator['defaultStateId'] ?? null) == $state->id)>
                        {{ $state->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="{{ $cityId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
            <select
                id="{{ $cityId }}"
                name="city_id"
                required
                data-repair-locator-city
                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                @disabled(! ($repairLocator['defaultStateId'] ?? null))
            >
                <option value="">انتخاب شهر</option>
                @if ($repairLocator['defaultStateId'] ?? null)
                    @foreach ($repairLocator['citiesByState'][$repairLocator['defaultStateId']] ?? [] as $city)
                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="flex flex-wrap gap-3 pt-1">
            <button type="submit" class="ps-btn-primary flex-1">نمایش تعمیرگاه‌ها</button>
            <button
                type="button"
                class="ps-btn-secondary"
                onclick="document.getElementById('{{ $modalId }}').close()"
            >
                انصراف
            </button>
        </div>
    </form>
</dialog>

@once
    @push('scripts')
        <script>
            (function () {
                const citiesByState = @json($repairLocator['citiesByState']);

                document.querySelectorAll('[data-repair-locator-state]').forEach(function (stateSelect) {
                    const form = stateSelect.closest('[data-repair-locator-form]');
                    const citySelect = form?.querySelector('[data-repair-locator-city]');

                    if (!citySelect) {
                        return;
                    }

                    stateSelect.addEventListener('change', function () {
                        const stateId = this.value;
                        const cities = citiesByState[stateId] || [];

                        citySelect.innerHTML = '<option value="">انتخاب شهر</option>';

                        cities.forEach(function (city) {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });

                        citySelect.disabled = !stateId;
                    });
                });
            })();
        </script>
    @endpush
@endonce
