<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    use ConfiguresModalRelationCreate;

    protected static ?string $title = 'لینک ها';
    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                $this->makeModalCreateAction('company_id', 'ساخت لینک جدید'),
            ])
            ->actions([
                DetachAction::make()->label('حذف لینک'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()->label('حذف لینک'),
                ]),
            ]);
    }
}
