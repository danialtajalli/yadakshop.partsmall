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
                <span class="text-sm font-medium text-ink">{{ $phone->type->label() }}</span>
                    <i class="{{ $phone->type->icon() }}" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    @else
        @foreach ($contacts as $contact)
            <li class="flex items-center justify-between gap-3 rounded-xl bg-surface px-3 py-2.5">
                <span class="text-sm font-medium text-ink">{{ $contact['label'] }}</span>
                <a
                    href="{{ $contact['url'] ?? 'tel:'.$contact['value'] }}"
                    @if (($contact['external'] ?? false)) target="_blank" rel="noopener" @endif
                    class="flex size-9 shrink-0 items-center justify-center rounded-lg text-base text-brand transition hover:bg-brand-soft hover:text-brand-dark"
                    title="{{ $contact['label'] }}"
                    aria-label="{{ $contact['label'] }}"
                >
                    <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    @endif
</ul>
