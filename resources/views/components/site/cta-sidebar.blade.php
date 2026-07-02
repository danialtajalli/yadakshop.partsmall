@props([
    'telegramTitle' => 'به کانال تلگرام پارتس‌مال بپیوندید',
    'telegramUrl' => '#',
    'signupUrl' => route('page.show', 'register'),
])

<aside {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <x-site.seller-signup-card :signup-url="$signupUrl" />
    <x-site.telegram-cta-card
        :title="$telegramTitle"
        :telegram-url="$telegramUrl"
    />
</aside>
