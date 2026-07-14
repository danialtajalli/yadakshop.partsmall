<?php

namespace App\Services\Contact\Didar;

readonly class DidarApiResponse
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public bool $ok,
        public int $status,
        public ?array $data,
        public ?string $error,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(int $status, array $data): self
    {
        return new self(true, $status, $data, null);
    }

    public static function failure(int $status, ?array $data, string $error): self
    {
        return new self(false, $status, $data, $error);
    }
}
