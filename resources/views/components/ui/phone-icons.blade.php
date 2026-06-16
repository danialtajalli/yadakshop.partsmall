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
                <p>{{ $phone->phone_number }}</p>
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
                <span class="text-sm font-medium text-ink">{{ $contact['label'] }}</span>
                    <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    @endif
</ul>
