<?php

namespace Tests\Unit\Support;

use App\Support\Search\SearchTextNormalizer;
use PHPUnit\Framework\TestCase;

class SearchTextNormalizerTest extends TestCase
{
    public function test_it_normalizes_persian_search_text(): void
    {
        $normalizer = new SearchTextNormalizer();

        $this->assertSame(
            'کیا سراتو ix35 f10 528i',
            $normalizer->normalize("كياـسِراتو، ix35 / F10 - 528i"),
        );
    }

    public function test_it_returns_useful_tokens(): void
    {
        $normalizer = new SearchTextNormalizer();

        $this->assertSame(['ix45', 'x5', 'k5'], $normalizer->tokens('IX45 / X5 - K5'));
    }
}
