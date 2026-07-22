<?php

namespace App\Support;

/** Shared debounce rules for catalog listing searches. */
final class CatalogSearch
{
    public const MIN_CHARS = 2;

    /** Debounce for remote (server) searches. */
    public const DEBOUNCE_MS = 1000;

    /** No delay for in-page client filtering. */
    public const CLIENT_DEBOUNCE_MS = 0;
}
