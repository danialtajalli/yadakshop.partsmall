@props([
    'signupUrl' => route('page.show', 'register'),
])

<aside {{ $attributes->merge(['class' => 'w-full shrink-0 md:w-72 lg:w-80']) }}>
    <div class="md:sticky md:top-24">
        <x-site.seller-signup-card :signup-url="$signupUrl" />
    </div>
</aside>
