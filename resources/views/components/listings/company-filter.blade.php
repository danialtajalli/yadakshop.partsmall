@props([
    'companies' => collect(),
    'selected' => null,
])

@if ($companies->isNotEmpty())
    <div class="mt-6 border-t border-line/80 pt-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
            {{-- Physical left (RTL end): selected company --}}
            <div class="order-2 min-w-0 sm:order-2">
                @if ($selected)
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2.5 rounded-xl border border-line bg-white/90 px-3 py-2 text-sm font-semibold text-ink shadow-sm backdrop-blur-sm">
                            @if (filled($selected->logo_url ?? null))
                                <img
                                    src="{{ $selected->logo_url }}"
                                    alt=""
                                    class="size-8 rounded-lg object-contain"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @endif
                            <span class="min-w-0">
                                <span class="block text-[11px] font-medium text-ink-muted">کمپانی سازنده فعال</span>
                                <span class="block truncate">{{ $selected->name }}</span>
                            </span>
                        </span>
                        <a
                            href="{{ route('shops.index') }}"
                            class="text-xs font-medium text-ink-muted transition hover:text-ink"
                        >
                            بازگشت به همه فروشگاه‌ها
                        </a>
                    </div>
                @else
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink">فیلتر بر اساس کمپانی سازنده</p>
                        <p class="mt-0.5 text-xs text-ink-muted">فروشگاه‌های مرتبط با هر کمپانی را ببینید</p>
                    </div>
                @endif
            </div>

            {{-- Physical right (RTL start): company select --}}
            <div class="order-1 min-w-0 w-full sm:order-1 sm:max-w-sm sm:shrink-0">
                <label for="shop-company-filter" class="sr-only">کمپانی سازنده</label>
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
                            همه کمپانی سازندهها
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
    </div>
@endif
