<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\AssociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'Comments';

    protected static ?string $relatedResource = CommentResource::class;

    protected static ?string $title = 'نظرات';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن نظر')
                ->url(fn () => CommentResource::getUrl('create', [
                    'shop_id' => $this->getOwnerRecord()->id,
                ])),
                AssociateAction::make()->label('اضافه کردن نظر'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف نظر'),
            ]);
    }
}
