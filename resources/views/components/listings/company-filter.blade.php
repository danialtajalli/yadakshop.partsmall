@props([
    'companies' => collect(),
    'selected' => null,
])

@if ($companies->isNotEmpty())
    <div class="mt-6 border-t border-line/80 pt-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-ink">فیلتر بر اساس برند</p>
                <p class="mt-0.5 text-xs text-ink-muted">فروشگاه‌های مرتبط با هر کمپانی را ببینید</p>
            </div>

            <div class="min-w-0 w-full sm:max-w-sm">
                <label for="shop-company-filter" class="sr-only">برند</label>
                <div class="ps-searchable-select">
                    <select
                        id="shop-company-filter"
                        data-shop-company-select
                        data-searchable-select
                        data-option-logos
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
                                @if (filled($company->logo_url ?? null))
                                    data-logo="{{ $company->logo_url }}"
                                @endif
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
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-2.5 py-1.5 text-xs font-medium text-ink">
                    @if (filled($selected->logo_url ?? null))
                        <img
                            src="{{ $selected->logo_url }}"
                            alt=""
                            class="size-5 rounded object-contain"
                            loading="lazy"
                            decoding="async"
                        >
                    @endif
                    <span class="text-ink-muted">برند فعال:</span>
                    {{ $selected->name }}
                </span>
                <a
                    href="{{ route('shops.index') }}"
                    class="text-xs font-medium text-ink-muted transition hover:text-ink"
                >
                    بازگشت به همه فروشگاه‌ها
                </a>
            </div>
        @endif
    </div>
@endif
