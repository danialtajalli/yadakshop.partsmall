@props([
    'shop',
    'comments',
    'commentsCount' => null,
    'averageRating' => null,
])

@php
    $commentsCount = $commentsCount ?? $comments->count();
@endphp

<section {{ $attributes->merge(['id' => 'comments', 'class' => 'scroll-mt-24']) }}>
    <x-ui.section-heading
        title="نظرات کاربران"
        :description="$commentsCount > 0
            ? number_format($commentsCount).' نظر ثبت‌شده از طرف خریداران و مراجعه‌کنندگان'
            : 'هنوز نظری برای این فروشگاه ثبت نشده است.'"
    />

    <x-shop.comment-success-modal />

    @if ($commentsCount > 0 && $averageRating)
        <div class="ps-comment-rating-summary">
            <svg class="ps-comment-rating-summary__icon" viewBox="0 0 20 20" aria-hidden="true">
                <path fill="#d4a017" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z" />
            </svg>
            <span>
                میانگین امتیاز
                <span class="ps-comment-rating-summary__value">{{ number_format($averageRating, 1) }}</span>
                از ۵
            </span>
        </div>
    @endif

    @if ($comments->isEmpty())
        <div class="rounded-2xl mb-4 border border-dashed border-line bg-white px-6 py-10 text-center">
            <p class="text-sm text-ink-muted">اولین نفری باشید که تجربه خود را از این فروشگاه به اشتراک می‌گذارد.</p>
        </div>
    @else
        <div class="space-y-4 mb-4">
            @foreach ($comments as $comment)
                <x-shop.comment-card :comment="$comment" />
            @endforeach
        </div>
    @endif

    <x-shop.comment-form :shop="$shop" />
</section>
