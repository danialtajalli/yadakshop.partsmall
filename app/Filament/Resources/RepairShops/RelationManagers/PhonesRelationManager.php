<?php

namespace App\Filament\Resources\RepairShops\RelationManagers;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use App\Filament\Resources\Phones\PhoneResource;
use App\Filament\Resources\Phones\Schemas\PhoneForm;
use Filament\Actions\DissociateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
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
                $this->makeModalCreateAction('repair_shop_id', 'افزودن تلفن')
                    ->schema(fn (Schema $schema): Schema => PhoneForm::configure($schema)),
            ])
            ->actions([
                EditAction::make()
                    ->label('ویرایش')
                    ->modal()
                    ->schema(fn (Schema $schema): Schema => PhoneForm::configure($schema)),
                DissociateAction::make()->label('حذف تلفن'),
            ])
            ->inverseRelationship('repairShop');
    }
}
