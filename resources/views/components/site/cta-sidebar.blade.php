<aside {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <x-site.seller-signup-card />
    <x-site.telegram-cta-card
        :title="$telegramTitle"
        :telegram-url="$telegramUrl"
    />
</aside>
