@props(['repairLocator'])

@php
    $modalId = 'product-repair-locator-modal-'.$repairLocator['category']->id;
    $stateId = 'repair-locator-state-'.$repairLocator['category']->id;
    $cityId = 'repair-locator-city-'.$repairLocator['category']->id;
@endphp

<article class="min-w-0">
    <button
        type="button"
        class="group flex w-full min-w-0 items-center justify-between gap-3 rounded-xl border border-line bg-white px-3 py-2.5 text-start transition hover:border-brand/30 hover:bg-brand-soft/30"
        onclick="document.getElementById('{{ $modalId }}').showModal()"
    >
        <span class="min-w-0 flex-1 text-start">
            <span class="block text-[11px] font-semibold leading-snug text-ink/80">
                خدمات {{ $repairLocator['category']->name }}
            </span>
        </span>
        <span class="shrink-0 rounded-full bg-brand px-2.5 py-1 text-[10px] font-bold text-white transition group-hover:bg-brand-dark">
            انتخاب محدوده
        </span>
    </button>
</article>

<x-ui.modal id="{{ $modalId }}" class="max-w-md">
    <div class="overflow-hidden rounded-t-2xl border-b border-line bg-linear-to-l from-gray-100 via-white px-5 py-4">
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
        data-cities-by-state='@json($repairLocator['citiesByState'])'
    >
        <input type="hidden" name="specialization_id" value="{{ $repairLocator['category']->id }}">

        <p class="rounded-xl bg-surface px-4 py-3 text-sm leading-7 text-ink-muted">
            استان و شهر خود را انتخاب کنید تا تعمیرگاه‌هایی که خدمات مرتبط با
            <span class="font-medium text-ink">{{ $repairLocator['category']->name }}</span>
            برای خودروی
            <span class="font-medium text-ink">{{ $repairLocator['carName'] }}</span>
            شما ارائه می دهند را ببینید.
        </p>

        <div class="grid grid-cols-2 gap-3">
            <div class="min-w-0">
                <label for="{{ $stateId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">استان</label>
                <div class="ps-searchable-select">
                    <select
                        id="{{ $stateId }}"
                        name="state_id"
                        required
                        data-repair-locator-state
                        data-searchable-select
                    >
                        <option value="">انتخاب استان</option>
                        @foreach ($repairLocator['states'] as $state)
                            <option value="{{ $state->id }}" @selected(($repairLocator['defaultStateId'] ?? null) == $state->id)>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="min-w-0">
                <label for="{{ $cityId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
                <div class="ps-searchable-select">
                    <select
                        id="{{ $cityId }}"
                        name="city_id"
                        data-repair-locator-city
                        data-searchable-select
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
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-1">
            <button type="submit" class="ps-btn-primary flex-1">نمایش تعمیرگاه‌ها</button>
        </div>
    </form>
</x-ui.modal>
