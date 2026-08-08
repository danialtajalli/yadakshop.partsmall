<?php

namespace Tests\Unit\Support;

use App\Models\Shop;
use App\Support\ShopQrCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopQrCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_qr_png_when_a_shop_is_created(): void
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        // Pad to pass the generator's minimum size check while keeping PNG magic bytes.
        $png = $png.str_repeat("\0", 120);

        Http::fake([
            'api.qrserver.com/*' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        $shop = Shop::create([
            'name' => 'فروشگاه تست QR',
            'slug' => 'TestQrShop',
            'order' => 1,
        ]);

        $path = ShopQrCodeGenerator::pathForSlug($shop->slug);

        $this->assertFileExists($path);
        $this->assertTrue(str_starts_with((string) file_get_contents($path), "\x89PNG\r\n\x1a\n"));

        @unlink($path);
    }

    public function test_profile_url_uses_configured_public_base(): void
    {
        config(['partsmall.qr_profile_base_url' => 'https://partsmall.ir']);

        $this->assertSame(
            'https://partsmall.ir/profile/MyShop',
            ShopQrCodeGenerator::profileUrl('MyShop'),
        );
    }
}
