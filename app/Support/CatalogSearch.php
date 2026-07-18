<?php

namespace App\Support;

/** Shared debounce rules for catalog listing searches. */
final class CatalogSearch
{
    public const MIN_CHARS = 2;

    public const DEBOUNCE_MS = 1000;
}
