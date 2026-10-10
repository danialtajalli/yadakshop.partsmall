<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        config(['trustedproxy.proxies' => ['172.30.50.0/24']]);

        Route::get('/_tests/proxy', fn (Request $request) => response()->json([
            'secure' => $request->isSecure(),
            'ip' => $request->ip(),
            'port' => $request->getPort(),
            'host' => $request->getHost(),
        ]));
    }

    public function test_docker_proxy_can_forward_https_and_client_ip(): void
    {
        $this->proxyRequest('172.30.50.10')->assertOk()->assertExactJson([
            'secure' => true,
            'ip' => '203.0.113.25',
            'port' => 443,
            'host' => 'partsmall.example',
        ]);
    }

    public function test_untrusted_peer_cannot_spoof_forwarded_headers(): void
    {
        $this->proxyRequest('198.51.100.10')->assertOk()->assertExactJson([
            'secure' => false,
            'ip' => '198.51.100.10',
            'port' => 80,
            'host' => 'partsmall.example',
        ]);
    }

    public function test_no_proxy_is_trusted_without_configuration(): void
    {
        config(['trustedproxy.proxies' => []]);

        $this->proxyRequest('172.30.50.10')->assertOk()->assertExactJson([
            'secure' => false,
            'ip' => '172.30.50.10',
            'port' => 80,
            'host' => 'partsmall.example',
        ]);
    }

    private function proxyRequest(string $remoteAddress): TestResponse
    {
        return $this->withServerVariables([
            'REMOTE_ADDR' => $remoteAddress,
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'partsmall.example',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.25',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
            'HTTP_X_FORWARDED_HOST' => 'spoofed.example',
        ])->get('http://partsmall.example/_tests/proxy');
    }
}
