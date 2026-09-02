<?php

namespace App\Filament\Resources\RequestLogs\Pages;

use App\Filament\Resources\RequestLogs\RequestLogResource;
use App\Filament\Resources\RequestLogs\Widgets\RequestLogStatusOverviewWidget;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Resources\Pages\ListRecords;

class ListRequestLogs extends ListRecords
{
    use HasFiltersAction;

    protected static ?string $title = 'لاگ درخواست‌ها';

    protected static string $resource = RequestLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('فیلتر بازه زمانی')
                ->modalHeading('فیلتر خلاصه وضعیت درخواست‌ها')
                ->slideOver(false)
                ->schema([
                    Select::make('range')
                        ->label('بازه زمانی')
                        ->options([
                            '10m' => '۱۰ دقیقه اخیر',
                            '1h' => '۱ ساعت اخیر',
                            '1d' => '۱ روز اخیر',
                            '1w' => '۱ هفته اخیر',
                            '1mo' => '۱ ماه اخیر',
                        ])
                        ->default('10m')
                        ->required()
                        ->native(false),
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RequestLogStatusOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
