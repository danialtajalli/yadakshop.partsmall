@props(['repairLocator'])

<div class="overflow-hidden rounded-2xl border-2 border-brand/35 bg-gradient-to-l from-brand-soft via-white to-white shadow-card ring-1 ring-brand/10">
    <div class="flex flex-col gap-5 p-5 sm:flex-col sm:items-center sm:justify-center sm:gap-6 sm:p-6">
        <div class="flex min-w-0 items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand text-white shadow-sm">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.88m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336 4.5 4.5 0 0 0-6.336-4.486c-.072 1.172-.088 2.402.14 3.743Z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand">تعمیرگاه نزدیک شما</p>
                <p class="mt-1 text-base font-bold text-ink sm:text-lg">
                    تعمیرگاه‌های {{ $repairLocator['category']->name }} برای {{ $repairLocator['carName'] }}
                </p>
                <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">
                    استان و شهر خود را انتخاب کنید و نزدیک‌ترین تعمیرگاه‌های تخصصی را پیدا کنید.
                </p>
            </div>
        </div>

        <button
            type="button"
            class="group flex w-full shrink-0 items-center justify-center gap-2.5 rounded-xl bg-brand px-6 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-brand-dark hover:shadow-lg active:scale-[0.98] sm:w-auto"
            onclick="document.getElementById('product-repair-locator-modal').showModal()"
        >
            <svg class="size-5 shrink-0 transition group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            {{ $repairLocator['buttonLabel'] }}
        </button>
    </div>
</div>

<dialog
    id="product-repair-locator-modal"
    class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-white p-0 shadow-2xl backdrop:bg-ink/40 open:animate-none"
>
    <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
        <h2 class="text-base font-bold text-ink">انتخاب محدوده</h2>
        <button
            type="button"
            class="flex size-8 items-center justify-center rounded-lg text-ink-muted transition hover:bg-surface hover:text-ink"
            onclick="document.getElementById('product-repair-locator-modal').close()"
            aria-label="بستن"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form
        method="GET"
        action="{{ route('repair-shops.index') }}"
        class="space-y-4 px-5 py-5"
        data-repair-locator-form
    >
        <input type="hidden" name="specialization_id" value="{{ $repairLocator['category']->id }}">

        <p class="text-sm leading-7 text-ink-muted">
            استان و شهر خود را انتخاب کنید تا تعمیرگاه‌های مرتبط با
            <span class="font-medium text-ink">{{ $repairLocator['category']->name }}</span>
            برای
            <span class="font-medium text-ink">{{ $repairLocator['carName'] }}</span>
            را ببینید.
        </p>

        <div>
            <label for="repair-locator-state" class="mb-1.5 block text-xs font-medium text-ink-muted">استان</label>
            <select
                id="repair-locator-state"
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
            <label for="repair-locator-city" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
            <select
                id="repair-locator-city"
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
            <button type="submit" class="ps-btn-primary flex-1">مشاهده تعمیرگاه‌ها</button>
            <button
                type="button"
                class="ps-btn-secondary"
                onclick="document.getElementById('product-repair-locator-modal').close()"
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
