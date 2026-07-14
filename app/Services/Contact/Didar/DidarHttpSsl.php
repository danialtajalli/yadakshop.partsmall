<?php

namespace App\Services\Contact\Didar;

class DidarHttpSsl
{
    /**
     * Guzzle verify option: CA bundle path, true, or false.
     */
    public function verifyOption(): bool|string
    {
        if (! config('contact.didar.verify_ssl', true)) {
            return false;
        }

        $configured = config('contact.didar.cafile');

        if (is_string($configured) && $configured !== '' && is_readable($configured)) {
            return $this->normalizePath($configured);
        }

        foreach ($this->candidateCaFiles() as $candidate) {
            if (is_readable($candidate)) {
                return $this->normalizePath($candidate);
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function candidateCaFiles(): array
    {
        $phpBinary = PHP_BINARY;
        $phpDir = dirname($phpBinary);

        return array_values(array_filter([
            ini_get('curl.cainfo') ?: null,
            ini_get('openssl.cafile') ?: null,
            $phpDir.DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'cacert.pem',
            'C:/wamp64/bin/php/'.PHP_VERSION.'/extras/ssl/cacert.pem',
            'C:/wamp64/bin/php/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION.'/extras/ssl/cacert.pem',
        ], static fn (?string $path): bool => is_string($path) && $path !== ''));
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
