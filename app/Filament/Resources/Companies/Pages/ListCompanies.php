<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCompanies extends ListRecords
{
    protected static ?string $title = 'لیست برندهای خودرو';
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('افزودن برند خودرو'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه برندهای خودرو'),
            'wage_strike' => Tab::make('خودروهای با ضریب اجرت')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('wage_strike', '>=', 1)),
        ];
    }
}
