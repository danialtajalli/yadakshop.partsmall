<?php

namespace Tests\Unit\Support;

use App\Models\Image;
use App\Support\ContentCacheInvalidator;
use App\Support\ContentCacheTag;
use Tests\TestCase;

class ContentCacheInvalidatorImageTest extends TestCase
{
    public function test_company_image_edits_invalidate_directory_filters_too(): void
    {
        $image = new Image(['company_id' => 1, 'alt' => 'Updated brand logo']);

        $this->assertSame(
            [ContentCacheTag::HOME, ContentCacheTag::CATALOG, ContentCacheTag::DIRECTORY],
            ContentCacheInvalidator::tagsFor($image),
        );
    }
}
