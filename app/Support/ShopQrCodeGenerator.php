<?php

namespace App\Support;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopQrCodeGenerator
{
    public static function pathForSlug(string $slug): string
    {
        return public_path('panel/assets/uploads/img/'.strtolower($slug).'qrcode.png');
    }

    public static function profileUrl(string $slug): string
    {
        $base = rtrim((string) config('partsmall.qr_profile_base_url', 'https://partsmall.ir'), '/');

        return $base.'/profile/'.$slug;
    }

    public static function generate(Shop $shop): bool
    {
        $slug = trim((string) $shop->slug);

        if ($slug === '') {
            return false;
        }

        $directory = dirname(self::pathForSlug($slug));

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            Log::warning('Shop QR directory could not be created.', [
                'shop_id' => $shop->id,
                'directory' => $directory,
            ]);

            return false;
        }

        $profileUrl = self::profileUrl($slug);
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data='.rawurlencode($profileUrl);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'PartsMall-QR-Generator/1.0'])
                ->get($apiUrl);
        } catch (\Throwable $exception) {
            Log::warning('Shop QR API request failed.', [
                'shop_id' => $shop->id,
                'slug' => $slug,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Shop QR API returned an error response.', [
                'shop_id' => $shop->id,
                'slug' => $slug,
                'status' => $response->status(),
            ]);

            return false;
        }

        $binary = $response->body();

        if (strlen($binary) < 100 || ! str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
            Log::warning('Shop QR API returned an invalid PNG.', [
                'shop_id' => $shop->id,
                'slug' => $slug,
            ]);

            return false;
        }

        $path = self::pathForSlug($slug);

        if (file_put_contents($path, $binary) === false) {
            Log::warning('Shop QR file could not be written.', [
                'shop_id' => $shop->id,
                'path' => $path,
            ]);

            return false;
        }

        return true;
    }
}
