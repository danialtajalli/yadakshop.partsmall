<?php

namespace App\Filament\Resources\ContactLeads;

use App\Filament\Resources\ContactLeads\Pages\EditContactLead;
use App\Filament\Resources\ContactLeads\Pages\ListContactLeads;
use App\Filament\Resources\ContactLeads\Schemas\ContactLeadForm;
use App\Filament\Resources\ContactLeads\Tables\ContactLeadsTable;
use App\Models\ContactLead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactLeadResource extends Resource
{
    protected static ?string $navigationLabel = 'درخواست‌های تماس';

    protected static ?int $navigationSort = 12;

    protected static ?string $model = ContactLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'phone';

    public static function form(Schema $schema): Schema
    {
        return ContactLeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactLeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactLeads::route('/'),
            'edit' => EditContactLead::route('/{record}/edit'),
        ];
    }
}
