@props(['listing', 'type' => 'shop'])

<article {{ $attributes->merge(['class' => 'ps-card-interactive relative flex h-full flex-col p-5']) }}>
    @if ($type === 'shop')
        <a href="{{ route('shop.profile', $listing->slug) }}" class="absolute inset-0 z-10 rounded-2xl" aria-label="مشاهده پروفایل {{ $listing->name }}"></a>
    @elseif ($type === 'repair_shop')
        <a href="{{ route('repair-shop.profile', $listing->slug) }}" class="absolute inset-0 z-10 rounded-2xl" aria-label="مشاهده پروفایل {{ $listing->name }}"></a>
    @elseif ($type === 'representation')
        <a href="{{ route('representation.profile', $listing->slug) }}" class="absolute inset-0 z-10 rounded-2xl" aria-label="مشاهده پروفایل {{ $listing->name }}"></a>
    @endif
    <div class="mb-4 flex items-start gap-4">
        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-soft to-accent-soft text-lg font-bold text-brand-dark ring-1 ring-line">
            @if ($listing->logo ?? null)
                <img src="{{ $listing->logo }}" alt="{{ $listing->name }}" class="size-full object-cover">
            @else
                {{ mb_substr($listing->name, 0, 1) }}
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-ink">{{ $listing->name }}</h3>

            @if ($type === 'shop' && $listing->secondary_name)
                <p class="truncate text-sm text-ink-muted">{{ $listing->secondary_name }}</p>
            @elseif ($type === 'repair_shop' && $listing->work_description)
                <p class="line-clamp-2 text-sm text-ink-muted">{{ $listing->work_description }}</p>
            @elseif ($type === 'representation' && $listing->company)
                <p class="truncate text-sm text-ink-muted">{{ $listing->company->name }}</p>
            @endif

            @if ($type === 'shop')
                <div class="mt-2 inline-flex items-center gap-1 rounded-lg bg-accent-soft px-2 py-0.5 text-xs font-medium text-accent">
                    @if ($listing->average_rating ?? null)
                        <svg class="size-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                        <span>{{ number_format($listing->average_rating, 1) }}</span>
                    @else
                        <span class="text-ink-muted">بدون امتیاز</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($type === 'repair_shop' && $listing->repairCategories?->isNotEmpty())
        <ul class="mb-4 flex flex-wrap gap-2">
            @foreach ($listing->repairCategories->take(3) as $category)
                <li class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-brand-dark">
                    {{ $category->name }}
                </li>
            @endforeach
        </ul>
    @endif

    @if ($type === 'representation' && $listing->service_type)
        <ul class="mb-4 flex flex-wrap gap-2">
            @foreach (array_slice(preg_split('/\s*,\s*/', $listing->service_type) ?: [], 0, 3) as $service)
                @if (trim($service) !== '')
                    <li class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-brand-dark">
                        {{ trim($service) }}
                    </li>
                @endif
            @endforeach
        </ul>
    @endif

    <div class="mt-auto space-y-1 text-sm text-ink-muted">
        @if ($listing->state)
            <p>
                {{ $listing->state->name }}
                @if ($type === 'representation' && $listing->city)
                    ، {{ $listing->city->name }}
                @endif
            </p>
        @endif
        @if ($listing->address)
            <p class="line-clamp-2">{{ $listing->address }}</p>
        @endif
    </div>
</article>
