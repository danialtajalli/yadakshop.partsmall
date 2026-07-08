@props([
    'phones' => null,
    'contacts' => [],
])

@php
    use App\Enums\PhoneType;
    use App\Support\ContactIcon;

    if ($phones) {
        $directPhoneSections = collect([PhoneType::Land, PhoneType::Mobile])
            ->map(function (PhoneType $type) use ($phones) {
                $items = $phones->filter(fn ($phone) => $phone->type === $type)->values();

                return $items->isEmpty()
                    ? null
                    : [
                        'kind' => 'type',
                        'type' => $type,
                        'items' => $items,
                    ];
            })
            ->filter()
            ->values();

        $messengerTypes = collect([
            PhoneType::Telegram,
            PhoneType::Whatsapp,
            PhoneType::Rubika,
            PhoneType::Eita,
            PhoneType::Soroush,
            PhoneType::Ble,
            PhoneType::Gap,
            PhoneType::Igap,
        ]);

        $messengerItems = $messengerTypes
            ->flatMap(function (PhoneType $type) use ($phones) {
                return $phones->filter(fn ($phone) => $phone->type === $type)->values();
            })
            ->values();

        $globalMessengerItems = $messengerItems
            ->filter(fn ($phone) => in_array($phone->type, [PhoneType::Telegram, PhoneType::Whatsapp], true))
            ->values();

        $localMessengerItems = $messengerItems
            ->reject(fn ($phone) => in_array($phone->type, [PhoneType::Telegram, PhoneType::Whatsapp], true))
            ->values();
    } else {
        $phoneGroups = collect($contacts)->groupBy(fn (array $contact) => $contact['label'] ?? $contact['kind'] ?? 'phone');
    }
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($phones)
        @foreach ($directPhoneSections as $section)
            @php
                $phoneType = $section['type'];
                $phonesInGroup = $section['items'];
                $isGrouped = $phonesInGroup->count() > 1;
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                <div class="border-b border-line bg-surface/50 px-3 py-2.5">
                    <span class="font-medium text-ink">{{ $phoneType->label() }}</span>
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($phonesInGroup as $phone)
                        <li>
                            <a
                                href="{{ $phoneType->actionUrl($phone->phone_number) }}"
                                class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-brand-soft/40"
                                dir="ltr"
                                aria-label="{{ $phoneType->label() }}: {{ $phone->phone_number }}"
                            >
                                <span class="tabular-nums text-ink">{{ $phone->phone_number }}</span>
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base leading-none text-brand">
                                    <i class="{{ $phoneType->icon() }}" aria-hidden="true"></i>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach

        @if ($messengerItems->isNotEmpty())
            <li class="overflow-hidden rounded-xl border border-line">
                <div class="border-b border-line bg-surface/50 px-3 py-2.5">
                    <span class="font-medium text-ink">پیام‌رسان‌ها</span>
                </div>

                @if ($globalMessengerItems->isNotEmpty())
                    <ul class="divide-y divide-line">
                        @foreach ($globalMessengerItems as $phone)
                            @php $phoneType = $phone->type; @endphp
                            <li>
                                <a
                                    href="{{ $phoneType->actionUrl($phone->phone_number) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                    aria-label="{{ $phoneType->label() }}: {{ $phone->phone_number }}"
                                >
                                    <span class="min-w-0 tabular-nums text-ink">{{ $phone->phone_number }}</span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base leading-none text-brand">
                                            <i class="{{ $phoneType->icon() }}" aria-hidden="true"></i>
                                        </span>
                                        <span class="text-xs font-medium text-ink">{{ $phoneType->label() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($globalMessengerItems->isNotEmpty() && $localMessengerItems->isNotEmpty())
                    <div class="border-t-[3px] border-ink/25" aria-hidden="true"></div>
                @endif

                @if ($localMessengerItems->isNotEmpty())
                    <ul class="divide-y divide-line">
                        @foreach ($localMessengerItems as $phone)
                            @php $phoneType = $phone->type; @endphp
                            <li>
                                <a
                                    href="{{ $phoneType->actionUrl($phone->phone_number) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                    aria-label="{{ $phoneType->label() }}: {{ $phone->phone_number }}"
                                >
                                    <span class="min-w-0 tabular-nums text-ink">{{ $phone->phone_number }}</span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base leading-none text-brand">
                                            <i class="{{ $phoneType->icon() }}" aria-hidden="true"></i>
                                        </span>
                                        <span class="text-xs font-medium text-ink">{{ $phoneType->label() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endif
    @else
        @foreach ($phoneGroups as $contactsInGroup)
            @php
                $contact = $contactsInGroup->first();
                $label = $contact['label'] ?? 'تماس';
                $isGrouped = $contactsInGroup->count() > 1;
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                <div class="border-b border-line bg-surface/50 px-3 py-2.5">
                    <span class="font-medium text-ink">{{ $label }}</span>
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($contactsInGroup as $groupContact)
                        <li>
                            <a
                                href="{{ $groupContact['url'] ?? 'tel:'.$groupContact['value'] }}"
                                @if ($groupContact['external'] ?? false) target="_blank" rel="noopener" @endif
                                class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-brand-soft/40"
                                dir="ltr"
                                aria-label="{{ $label }}: {{ $groupContact['value'] }}"
                            >
                                <span class="tabular-nums text-ink">{{ $groupContact['value'] }}</span>
                                <span class="flex shrink-0 items-center gap-2">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base leading-none text-brand">
                                        <i class="{{ ContactIcon::forKind($contact['kind'] ?? 'phone') }}" aria-hidden="true"></i>
                                    </span>
                                    <span class="text-xs font-medium text-ink">{{ $label }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    @endif
</ul>
