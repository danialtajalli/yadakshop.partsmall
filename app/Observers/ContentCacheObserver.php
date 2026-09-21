<?php

namespace App\Observers;

use App\Support\ContentCacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class ContentCacheObserver
{
    public function saved(Model $model): void
    {
        ContentCacheInvalidator::forModel($model);
    }

    public function deleted(Model $model): void
    {
        ContentCacheInvalidator::forModel($model);
    }

    public function restored(Model $model): void
    {
        ContentCacheInvalidator::forModel($model);
    }
}
