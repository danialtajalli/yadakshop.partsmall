@props([
    'companies' => collect(),
    'selected' => null,
])

@if ($companies->isNotEmpty())
    <div class="mt-5 border-t border-brand-soft/25 pt-4">
        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
            <div class="flex min-w-0 items-center gap-2 sm:shrink-0">
                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-soft/15 text-brand-soft" aria-hidden="true">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-brand-soft">نمایش فروشگاه های هر کمپانی</p>
                </div>
            </div>

            <div class="min-w-0 flex-1 sm:max-w-xs sm:ms-auto">
                <label for="shop-company-filter" class="sr-only">برند</label>
                <div class="ps-searchable-select">
                    <select
                        id="shop-company-filter"
                        data-shop-company-select
                        data-searchable-select
                    >
                        <option
                            value=""
                            data-url="{{ route('shops.index') }}"
                            @selected($selected === null)
                        >
                            همه برندها
                        </option>
                        @foreach ($companies as $company)
                            <option
                                value="{{ $company->slug }}"
                                data-url="{{ route('shops.company', $company) }}"
                                @selected($selected?->is($company))
                            >
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if ($selected)
            <div class="mt-2.5 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-brand-soft/10 px-2.5 py-1 text-xs font-medium text-ink">
                    <span class="text-ink-muted">برند فعال:</span>
                    {{ $selected->name }}
                </span>
                <a
                    href="{{ route('shops.index') }}"
                    class="text-xs font-medium text-brand-soft transition hover:text-brand-dark"
                >
                    بازگشت به همه فروشگاه‌ها
                </a>
            </div>
        @endif
    </div>
@endif
