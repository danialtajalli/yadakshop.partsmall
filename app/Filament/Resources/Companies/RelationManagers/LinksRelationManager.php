<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static ?string $title = 'لینک ها';
    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('ساخت لینک جدید')
                ->url(fn () => LinkResource::getUrl('create', [
                    'company_id' => $this->getOwnerRecord()->id,
                ])),
                AttachAction::make()->label('اضافه کردن لینک'),
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
