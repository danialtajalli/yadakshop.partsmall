@props([
    'shops',
    'companies' => collect(),
])

@if ($shops->isNotEmpty())
    @php
        $primaryCompany = $companies->first();
        $allShopsUrl = $primaryCompany
            ? route('shops.company', $primaryCompany->slug)
            : route('shops.index');
        $footerLabel = $primaryCompany
            ? 'سایر فروشگاه‌های '.$primaryCompany->name
            : 'مشاهده همه فروشگاه‌ها';
        $visibleShops = $shops->take(5);
    @endphp

    <section class="ps-card p-5" data-related-shops>
        <h2 class="mb-4 text-base font-bold text-ink">فروشگاه‌های مرتبط</h2>

        <ul class="-mx-2 space-y-0.5">
            @foreach ($visibleShops as $relatedShop)
                <li>
                    <a
                        href="{{ route('shop.profile', $relatedShop->slug) }}"
                        class="group flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-surface"
                    >
                        <x-ui.company-logo
                            :name="$relatedShop->name"
                            :logo-url="$relatedShop->logo ?? null"
                            size="xs"
                            class="shrink-0 ring-1 ring-line"
                        />

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium leading-5 text-ink group-hover:text-brand">
                                {{ $relatedShop->name }}
                            </span>
                            @if ($relatedShop->city?->name)
                                <span class="mt-0.5 block truncate text-[11px] leading-4 text-ink-muted/70">
                                    {{ $relatedShop->city->name }}
                                </span>
                            @endif
                        </span>

                        <i
                            class="fa-solid fa-angle-left text-[10px] text-ink-muted/40 transition group-hover:-translate-x-0.5 group-hover:text-brand"
                            aria-hidden="true"
                        ></i>
                    </a>
                </li>
            @endforeach
        </ul>

        <a
            href="{{ $allShopsUrl }}"
            class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-ink-muted transition hover:text-brand"
        >
            <span>{{ $footerLabel }}</span>
            <i class="fa-solid fa-arrow-left text-[10px]" aria-hidden="true"></i>
        </a>
    </section>
@endif
