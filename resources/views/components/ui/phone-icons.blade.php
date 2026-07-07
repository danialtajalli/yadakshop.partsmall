@props([
    'phones' => null,
    'contacts' => [],
])

@php
    use App\Enums\PhoneType;
    use App\Support\ContactIcon;

    if ($phones) {
        $phoneGroups = $phones->groupBy(fn ($phone) => $phone->type->value);
    } else {
        $phoneGroups = collect($contacts)->groupBy(fn (array $contact) => $contact['label'] ?? $contact['kind'] ?? 'phone');
    }
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($phones)
        @foreach ($phoneGroups as $phonesInGroup)
            @php
                $phoneType = $phonesInGroup->first()->type;
                $isGrouped = $phonesInGroup->count() > 1;
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                @if ($isGrouped)
                    <div class="flex items-center justify-between gap-3 border-b border-line bg-surface/50 px-3 py-2.5">
                        <span class="font-medium text-ink">{{ $phoneType->label() }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-base text-brand">
                            <i class="{{ $phoneType->icon() }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($phonesInGroup as $phone)
                            <li>
                                <a
                                    href="{{ $phoneType->actionUrl($phone->phone_number) }}"
                                    @if ($phoneType !== PhoneType::Land && $phoneType !== PhoneType::Mobile) target="_blank" rel="noopener" @endif
                                    class="block px-3 py-2.5 text-sm tabular-nums text-ink transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                    aria-label="{{ $phoneType->label() }}: {{ $phone->phone_number }}"
                                >
                                    {{ $phone->phone_number }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    @php $phone = $phonesInGroup->first(); @endphp
                    <a
                        href="{{ $phoneType->actionUrl($phone->phone_number) }}"
                        @if ($phoneType !== PhoneType::Land && $phoneType !== PhoneType::Mobile) target="_blank" rel="noopener" @endif
                        class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                        title="{{ $phoneType->label() }}"
                        aria-label="{{ $phoneType->label() }}"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium text-ink">{{ $phoneType->label() }}</span>
                            <span class="mt-0.5 block tabular-nums text-ink-muted" dir="ltr">{{ $phone->phone_number }}</span>
                        </span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                            <i class="{{ $phoneType->icon() }}" aria-hidden="true"></i>
                        </span>
                    </a>
                @endif
            </li>
        @endforeach
    @else
        @foreach ($phoneGroups as $contactsInGroup)
            @php
                $contact = $contactsInGroup->first();
                $label = $contact['label'] ?? 'تماس';
                $isGrouped = $contactsInGroup->count() > 1;
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                @if ($isGrouped)
                    <div class="flex items-center justify-between gap-3 border-b border-line bg-surface/50 px-3 py-2.5">
                        <span class="font-medium text-ink">{{ $label }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-base text-brand">
                            <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($contactsInGroup as $groupContact)
                            <li>
                                <a
                                    href="{{ $groupContact['url'] ?? 'tel:'.$groupContact['value'] }}"
                                    @if ($groupContact['external'] ?? false) target="_blank" rel="noopener" @endif
                                    class="block px-3 py-2.5 text-sm tabular-nums text-ink transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                    aria-label="{{ $label }}: {{ $groupContact['value'] }}"
                                >
                                    {{ $groupContact['value'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <a
                        href="{{ $contact['url'] ?? 'tel:'.$contact['value'] }}"
                        @if ($contact['external'] ?? false) target="_blank" rel="noopener" @endif
                        class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                        title="{{ $label }}"
                        aria-label="{{ $label }}"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium text-ink">{{ $label }}</span>
                            <span class="mt-0.5 block tabular-nums text-ink-muted" dir="ltr">{{ $contact['value'] }}</span>
                        </span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                            <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                        </span>
                    </a>
                @endif
            </li>
        @endforeach
    @endif
</ul>
