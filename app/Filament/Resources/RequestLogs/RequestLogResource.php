<?php

namespace App\Filament\Resources\RequestLogs;

use App\Filament\Resources\RequestLogs\Pages\ListRequestLogs;
use App\Filament\Resources\RequestLogs\Tables\RequestLogsTable;
use App\Models\RequestLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

class RequestLogResource extends Resource
{
    protected static ?string $navigationLabel = 'لاگ درخواست‌ها';

    protected static ?int $navigationSort = 99;
    protected static UnitEnum|string|null $navigationGroup = 'قسمت های جدید';

    protected static ?string $model = RequestLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $recordTitleAttribute = 'path';

    protected static ?string $modelLabel = 'لاگ درخواست';

    protected static ?string $pluralModelLabel = 'لاگ درخواست‌ها';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return RequestLogsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return SchemaFacade::hasTable('request_logs') && parent::canAccess();
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
            'index' => ListRequestLogs::route('/'),
        ];
    }
}
