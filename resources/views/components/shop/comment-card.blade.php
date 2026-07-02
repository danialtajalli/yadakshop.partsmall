@props([
    'comment',
])

@php
    $authorName = $comment->fullname ?: 'کاربر';
@endphp

<article {{ $attributes->merge(['class' => 'ps-card p-5']) }}>
    <div class="flex items-start gap-3.5">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-surface text-sm font-bold text-brand" aria-hidden="true">
            {{ mb_substr($authorName, 0, 1) }}
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-ink">{{ $authorName }}</p>
                    @if ($comment->created_at)
                        <time
                            datetime="{{ $comment->created_at->toIso8601String() }}"
                            class="mt-0.5 block text-xs text-ink-muted"
                        >
                            {{ $comment->created_at->format('Y/m/d') }}
                        </time>
                    @endif
                </div>

                <x-shop.comment-rating :rating="$comment->rating" class="shrink-0" />
            </div>

            @if ($comment->body)
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink-muted">{{ $comment->body }}</p>
            @endif
        </div>
    </div>
</article>
