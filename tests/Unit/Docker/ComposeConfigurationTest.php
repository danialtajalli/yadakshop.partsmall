<?php

namespace Tests\Unit\Docker;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ComposeConfigurationTest extends TestCase
{
    public function test_php_image_supports_the_pdo_mysql_configuration(): void
    {
        $dockerfile = file_get_contents(dirname(__DIR__, 3).'/Dockerfile');

        $this->assertMatchesRegularExpression('/^ARG PHP_VERSION=8\.4$/m', $dockerfile);
    }

    public function test_laravel_directories_are_created_before_artisan_boots(): void
    {
        $dockerfile = file_get_contents(dirname(__DIR__, 3).'/Dockerfile');

        foreach (['development', 'production'] as $stage) {
            $this->assertMatchesRegularExpression(
                '/FROM php-base AS '.$stage.'\R(?:(?!\R(?:FROM|RUN)).)*\R'
                .'RUN mkdir -p (?:(?!\R(?:FROM|RUN)).)*bootstrap\/cache'
                .'(?:(?!\R(?:FROM|RUN)).)*php artisan package:discover/s',
                $dockerfile,
            );
        }
    }

    public function test_both_php_stages_require_the_custom_pagination_view(): void
    {
        $dockerfile = file_get_contents(dirname(__DIR__, 3).'/Dockerfile');

        $this->assertSame(2, substr_count(
            $dockerfile,
            'test -f resources/views/vendor/pagination/tailwind.blade.php',
        ));
    }

    public function test_both_php_stages_require_the_admin_editor_assets(): void
    {
        $dockerfile = file_get_contents(dirname(__DIR__, 3).'/Dockerfile');

        $this->assertSame(2, substr_count($dockerfile, 'test -f public/vendor/tinymce/tinymce.min.js'));
    }

    public function test_production_is_isolated_and_keeps_workers_opt_in(): void
    {
        $config = $this->configuration('prod');

        foreach (['app', 'setup', 'queue'] as $service) {
            $environment = $config['services'][$service]['environment'];
            $this->assertSame('mysql', $environment['DB_HOST']);
            $this->assertSame('3306', $environment['DB_PORT']);
            $this->assertSame('production', $environment['APP_ENV']);
            $this->assertSame('false', $environment['APP_DEBUG']);
            $this->assertSame('true', $environment['SESSION_SECURE_COOKIE']);
            $this->assertSame('172.30.50.0/24', $environment['TRUSTED_PROXIES']);
            $this->assertSame('redis', $environment['CACHE_STORE']);
            $this->assertSame('redis', $environment['QUEUE_CONNECTION']);
            $this->assertSame('redis-cache', $environment['REDIS_CACHE_HOST']);
            $this->assertSame('redis-queue', $environment['REDIS_QUEUE_HOST']);
            $this->assertSame('180', $environment['REDIS_QUEUE_RETRY_AFTER']);
        }

        $this->assertSame(['workers'], $config['services']['queue']['profiles']);
        $this->assertCount(1, $config['services']['web']['ports']);
        $this->assertSame('127.0.0.1', $config['services']['web']['ports'][0]['host_ip']);
        $this->assertSame('8000', $config['services']['web']['ports'][0]['published']);
        $this->assertSame('127.0.0.1', $config['services']['mysql']['ports'][0]['host_ip']);
        $this->assertSame('3307', $config['services']['mysql']['ports'][0]['published']);
        $this->assertSame(3306, $config['services']['mysql']['ports'][0]['target']);
        $this->assertArrayNotHasKey('ports', $config['services']['app']);
        $this->assertArrayNotHasKey('vite', $config['services']);

        foreach (['mysql', 'storage', 'uploads', 'redis-queue'] as $volume) {
            $this->assertTrue($config['volumes'][$volume]['external']);
            $this->assertSame('partsmall-prod-'.$volume, $config['volumes'][$volume]['name']);
        }

        foreach (['redis-cache', 'redis-queue'] as $service) {
            $this->assertArrayNotHasKey('ports', $config['services'][$service]);
            $this->assertArrayHasKey('healthcheck', $config['services'][$service]);
        }
        $cacheCommand = $config['services']['redis-cache']['command'];
        $queueCommand = $config['services']['redis-queue']['command'];
        $this->assertContains('allkeys-lru', $cacheCommand);
        $this->assertContains('noeviction', $queueCommand);
        $this->assertContains('--appendonly', $queueCommand);
        $this->assertContains('everysec', $queueCommand);
        $this->assertContains('--binlog-expire-logs-seconds=2592000', $config['services']['mysql']['command']);
        foreach (['app', 'web', 'queue', 'mysql', 'redis-cache', 'redis-queue'] as $service) {
            $this->assertSame('20m', $config['services'][$service]['logging']['options']['max-size']);
            $this->assertSame('5', $config['services'][$service]['logging']['options']['max-file']);
        }
    }

    public function test_development_does_not_publish_mysql_or_use_production_volumes(): void
    {
        $config = $this->configuration('dev');

        $this->assertArrayNotHasKey('ports', $config['services']['mysql']);
        $this->assertArrayNotHasKey('profiles', $config['services']['queue']);
        $this->assertArrayHasKey('vite', $config['services']);
        $this->assertSame('local', $config['services']['app']['environment']['APP_ENV']);
        $this->assertEmpty($config['networks']['default']['ipam']['config'] ?? []);

        foreach (['mysql', 'storage', 'uploads', 'redis-queue'] as $volume) {
            $this->assertFalse($config['volumes'][$volume]['external'] ?? false);
            $this->assertSame('partsmall-dev_'.$volume, $config['volumes'][$volume]['name']);
        }
    }

    private function configuration(string $mode): array
    {
        $root = dirname(__DIR__, 3);
        $version = new Process(['docker', 'compose', 'version'], $root);
        $version->run();

        if (! $version->isSuccessful()) {
            $this->markTestSkipped('Docker Compose CLI is not available.');
        }

        $envFile = $mode === 'prod' ? '.env.production.example' : '.env.docker.example';
        $process = new Process([
            'docker', 'compose', '--env-file', $envFile,
            '--project-name', 'partsmall-'.$mode,
            '-f', 'compose.yaml', '-f', 'compose.'.$mode.'.yaml',
            '--profile', '*', 'config', '--format', 'json',
        ], $root, [
            'DOCKER_ENV_FILE' => $envFile,
            'DB_PASSWORD' => 'configuration-test-only',
            'MYSQL_ROOT_PASSWORD' => 'configuration-test-only',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'WEB_BIND_ADDRESS' => '0.0.0.0',
            'WEB_PORT' => '8000',
            'MYSQL_ADMIN_PORT' => '3307',
            'DOCKER_SUBNET' => '172.30.50.0/24',
            'PRODUCTION_VOLUME_PREFIX' => 'partsmall-prod',
        ]);
        $process->mustRun();

        return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    }
}
