<?php

namespace Tests\Unit\Support;

use App\Support\Legacy\LegacyInsertParser;
use PHPUnit\Framework\TestCase;

class LegacyInsertParserTest extends TestCase
{
    public function test_it_parses_all_shop_rows_from_sql_dump(): void
    {
        $sql = file_get_contents(dirname(__DIR__, 3).'/partsmall_db.sql');

        $this->assertNotFalse($sql);

        $shops = (new LegacyInsertParser($sql ?: ''))->rows('shop');

        $this->assertCount(353, $shops);
        $this->assertSame('1,2', $shops[0]['cat']);
    }
}
