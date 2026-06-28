<aside {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <x-site.telegram-cta-card
        :title="$telegramTitle"
        :telegram-url="$telegramUrl"
    />
</aside>
