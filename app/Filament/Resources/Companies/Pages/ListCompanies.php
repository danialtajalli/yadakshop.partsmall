<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCompanies extends ListRecords
{
    protected static ?string $title = 'لیست کمپانی ها';
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('افزودن کمپانی'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه کمپانی ها'),
            'wage_strike' => Tab::make('کمپانی های با ضریب اجرت')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wage_strike', '>=', 1)),
        ];
    }
}
