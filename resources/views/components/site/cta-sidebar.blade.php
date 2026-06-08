@props([
    'telegramTitle' => 'به گروه تلگرام چری تیگو ۵ سواران بپیوندید',
    'telegramUrl' => config('partsmall.telegram_url'),
])

<aside {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <x-site.seller-signup-card />
    <x-site.telegram-cta-card
        :title="$telegramTitle"
        :telegram-url="$telegramUrl"
    />
</aside>
