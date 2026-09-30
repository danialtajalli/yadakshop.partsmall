<?php

namespace App\Console\Commands;

use App\Support\ContentCacheInvalidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ClearApplicationCachesCommand extends Command
{
    protected $signature = 'partsmall:cache-clear
                            {--content-only : Only flush tagged content caches (home, catalog, …)}
                            {--no-views : Do not clear compiled Blade views}
                            {--framework : Also clear config / route / event / Filament component caches}';

    protected $description = 'Clear application / Redis content cache and compiled Blade views';

    public function handle(): int
    {
        if ($this->option('content-only')) {
            ContentCacheInvalidator::flushAll();
            $this->info('Content cache tags flushed.');

            return self::SUCCESS;
        }

        $this->flushApplicationCache();
        ContentCacheInvalidator::flushAll();
        $this->info('Content cache tags flushed.');

        if (! $this->option('no-views')) {
            $this->runArtisanQuietly('view:clear', 'Compiled views cleared.');
        }

        if ($this->option('framework')) {
            foreach ([
                'config:clear' => 'Config cache cleared.',
                'route:clear' => 'Route cache cleared.',
                'event:clear' => 'Event cache cleared.',
                'filament:clear-cached-components' => 'Filament component cache cleared.',
            ] as $command => $message) {
                $this->runArtisanQuietly($command, $message);
            }
        }

        $this->comment('If phone labels still look wrong, run: php artisan migrate');

        return self::SUCCESS;
    }

    private function flushApplicationCache(): void
    {
        try {
            Cache::flush();
            $this->info('Application cache flushed ('.config('cache.default').').');
        } catch (Throwable $e) {
            $this->warn('Could not flush cache store: '.$e->getMessage());
            $this->warn('Try Redis/file store, or start MySQL if CACHE_STORE=database.');
        }
    }

    private function runArtisanQuietly(string $command, string $successMessage): void
    {
        try {
            Artisan::call($command);
            $output = trim(Artisan::output());
            if ($output !== '') {
                $this->line($output);
            }
            $this->info($successMessage);
        } catch (Throwable $e) {
            $this->warn("Skipped {$command}: ".$e->getMessage());
        }
    }
}