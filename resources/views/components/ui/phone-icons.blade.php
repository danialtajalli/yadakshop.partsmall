@props([
    'phones' => null,
    'contacts' => [],
])

@php
    use App\Enums\PhoneType;
    use App\Support\ContactIcon;
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($phones)
        @foreach ($phones as $phone)
            <li>
                <a
                    href="{{ $phone->type->actionUrl($phone->phone_number) }}"
                    @if ($phone->type !== PhoneType::Land && $phone->type !== PhoneType::Mobile) target="_blank" rel="noopener" @endif
                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                    title="{{ $phone->type->label() }}"
                    aria-label="{{ $phone->type->label() }}"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium text-ink">{{ $phone->type->label() }}</span>
                        <span class="mt-0.5 block tabular-nums text-ink-muted" dir="ltr">{{ $phone->phone_number }}</span>
                    </span>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                        <i class="{{ $phone->type->icon() }}" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        @endforeach
    @else
        @foreach ($contacts as $contact)
            <li>
                <a
                    href="{{ $contact['url'] ?? 'tel:'.$contact['value'] }}"
                    @if (($contact['external'] ?? false)) target="_blank" rel="noopener" @endif
                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                    title="{{ $contact['label'] }}"
                    aria-label="{{ $contact['label'] }}"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium text-ink">{{ $contact['label'] }}</span>
                        <span class="mt-0.5 block tabular-nums text-ink-muted" dir="ltr">{{ $contact['value'] }}</span>
                    </span>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                        <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        @endforeach
    @endif
</ul>
