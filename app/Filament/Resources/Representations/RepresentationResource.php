<?php

namespace App\Filament\Resources\Representations;

use App\Filament\Resources\Representations\Pages\CreateRepresentation;
use App\Filament\Resources\Representations\Pages\EditRepresentation;
use App\Filament\Resources\Representations\Pages\ListRepresentations;
use App\Filament\Resources\Representations\Schemas\RepresentationForm;
use App\Filament\Resources\Representations\Tables\RepresentationsTable;
use App\Models\Representation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RepresentationResource extends Resource
{
    protected static ?string $navigationLabel = 'نمایندگی ها';
    protected static ?int $navigationSort = 12;
    protected static ?string $model = Representation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RepresentationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepresentationsTable::configure($table);
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
            'index' => ListRepresentations::route('/'),
            'create' => CreateRepresentation::route('/create'),
            'edit' => EditRepresentation::route('/{record}/edit'),
        ];
    }
}
