<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const WHITESPACE = '/[\s\x{00A0}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]+/u';

    public function up(): void
    {
        DB::table('phones')
            ->select(['id', 'phone_number'])
            ->orderBy('id')
            ->chunkById(500, function ($phones): void {
                foreach ($phones as $phone) {
                    $clean = preg_replace(self::WHITESPACE, '', (string) $phone->phone_number);

                    if ($clean !== null && $clean !== $phone->phone_number) {
                        DB::table('phones')
                            ->where('id', $phone->id)
                            ->update(['phone_number' => $clean]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible: original spacing is not kept.
    }
};
