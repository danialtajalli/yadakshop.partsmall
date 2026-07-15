<?php

namespace App\Filament\Resources\RepairShops\RelationManagers;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use App\Filament\Resources\Phones\PhoneResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PhonesRelationManager extends RelationManager
{
    use ConfiguresModalRelationCreate;

    protected static ?string $title = 'تلفن ها';
    protected static string $relationship = 'phones';

    protected static ?string $relatedResource = PhoneResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                $this->makeModalCreateAction('repair_shop_id', 'افزودن تلفن'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف تلفن'),
            ])
            ->inverseRelationship('repairShop');
    }
}
