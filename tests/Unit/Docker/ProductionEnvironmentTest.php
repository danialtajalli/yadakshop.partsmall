<?php

namespace Tests\Unit\Docker;

use Dotenv\Dotenv;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ProductionEnvironmentTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/partsmall-env-test-'.bin2hex(random_bytes(8));
        mkdir($this->directory.'/storage/app/public', 0700, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_preparation_preserves_live_settings_and_does_not_change_source(): void
    {
        $source = <<<'ENV'
APP_NAME="Partsmall Live"
APP_KEY=base64:original-key
APP_URL=https://partsmall.example
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=partsmall_live
DB_USERNAME=live-account
DB_PASSWORD='live-$password#with punctuation'
QUEUE_CONNECTION=redis
CACHE_STORE=redis
MAIL_PASSWORD='mail-$password#quoted'
MAIL_FROM_NAME="${APP_NAME}"
DIDAR_API_KEY='original-didar-key'
ARCAPTCHA_SECRET_KEY='original-captcha-key'
ENV;
        file_put_contents($this->directory.'/.env', $source);

        $process = $this->prepare();
        $this->assertSame(0, $process->getExitCode());
        $this->assertSame($source, file_get_contents($this->directory.'/.env'));
        $live = Dotenv::parse($source);
        $production = Dotenv::parse(file_get_contents($this->directory.'/.env.production'));

        foreach (['APP_NAME', 'APP_KEY', 'APP_URL', 'DB_DATABASE', 'MAIL_PASSWORD', 'MAIL_FROM_NAME', 'DIDAR_API_KEY', 'ARCAPTCHA_SECRET_KEY'] as $key) {
            $this->assertSame($live[$key], $production[$key]);
        }

        $this->assertSame('mysql', $production['DB_HOST']);
        $this->assertSame('8.0', $production['MYSQL_VERSION']);
        $this->assertSame('redis', $production['QUEUE_CONNECTION']);
        $this->assertSame('redis', $production['CACHE_STORE']);
        $this->assertSame('redis-cache', $production['REDIS_CACHE_HOST']);
        $this->assertSame('redis-queue', $production['REDIS_QUEUE_HOST']);
        $this->assertSame('queue', $production['REDIS_QUEUE_CONNECTION']);
        $this->assertSame('180', $production['REDIS_QUEUE_RETRY_AFTER']);
        $this->assertSame('true', $production['SESSION_SECURE_COOKIE']);
        $this->assertSame(realpath($this->directory.'/storage/app/public'), $production['UPLOAD_SOURCE']);
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $production['DB_PASSWORD']);
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $production['MYSQL_ROOT_PASSWORD']);
        $this->assertNotSame($production['DB_PASSWORD'], $production['MYSQL_ROOT_PASSWORD']);
        $this->assertStringNotContainsString($production['DB_PASSWORD'], $process->getOutput().$process->getErrorOutput());

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertSame(0600, fileperms($this->directory.'/.env.production') & 0777);
        }
    }

    public function test_existing_target_is_not_overwritten_or_rotated(): void
    {
        file_put_contents($this->directory.'/.env.production', 'existing production credentials');

        $this->assertSame(0, $this->prepare()->getExitCode());
        $this->assertSame('existing production credentials', file_get_contents($this->directory.'/.env.production'));
    }

    public function test_invalid_source_does_not_create_target_or_expose_input(): void
    {
        file_put_contents($this->directory.'/.env', "APP_KEY=secret malformed value\n");

        $process = $this->prepare();
        $this->assertSame(1, $process->getExitCode());
        $this->assertFileDoesNotExist($this->directory.'/.env.production');
        $this->assertStringNotContainsString('secret malformed value', $process->getOutput().$process->getErrorOutput());
    }

    private function prepare(): Process
    {
        $process = new Process([
            PHP_BINARY, dirname(__DIR__, 3).'/docker/init-production-env.php',
            $this->directory.'/.env', $this->directory.'/.env.production', '8.0',
        ]);
        $process->run();

        return $process;
    }
}
