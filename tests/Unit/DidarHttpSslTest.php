<?php

namespace Tests\Unit;

use App\Services\Contact\Didar\DidarHttpSsl;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DidarHttpSslTest extends TestCase
{
    public function test_uses_configured_cafile_when_readable(): void
    {
        Config::set('contact.didar.cafile', __FILE__);
        Config::set('contact.didar.verify_ssl', true);

        $this->assertSame(str_replace('\\', '/', __FILE__), (new DidarHttpSsl())->verifyOption());
    }

    public function test_can_disable_verification_via_config(): void
    {
        Config::set('contact.didar.cafile', null);
        Config::set('contact.didar.verify_ssl', false);

        $this->assertFalse((new DidarHttpSsl())->verifyOption());
    }
}
