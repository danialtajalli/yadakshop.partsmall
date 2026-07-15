<?php

namespace App\Filament\Resources\Images\Concerns;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use Filament\Actions\CreateAction;

trait ConfiguresImagesRelationManager
{
    use ConfiguresModalRelationCreate;

    protected function makeCreateImageAction(string $ownerForeignKey, string $label = 'افزودن تصویر'): CreateAction
    {
        return $this->makeModalCreateAction($ownerForeignKey, $label);
    }
}
