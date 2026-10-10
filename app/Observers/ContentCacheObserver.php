<?php

namespace App\Observers;

use App\Support\ContentCacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class ContentCacheObserver
{
    public function created(Model $model): void
    {
        ContentCacheInvalidator::forModel($model);
    }

    public function updated(Model $model): void
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
