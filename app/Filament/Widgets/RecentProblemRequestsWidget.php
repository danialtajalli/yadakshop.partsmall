<?php

namespace App\Filament\Widgets;

use App\Models\RequestLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Schema;

class RecentProblemRequestsWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Schema::hasTable('request_logs');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('درخواست‌های مشکل‌دار اخیر')
            ->query(
                RequestLog::query()
                    ->where(fn ($query) => $query
                        ->where('is_reportable_status', true)
                        ->orWhere('log_type', 'reportable_exception'))
                    ->latest('occurred_at')
            )
            ->poll('11s')
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('زمان')
                    ->jalaliDateTime()
                    ->sortable(),
                TextColumn::make('log_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'incoming_request' => 'درخواست',
                        'reportable_response' => 'پاسخ گزارش‌پذیر',
                        'reportable_exception' => 'خطا',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'reportable_exception' => 'danger',
                        'reportable_response' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status_code')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('method')
                    ->label('متد')
                    ->badge(),
                TextColumn::make('path')
                    ->label('مسیر')
                    ->limit(70)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('duration_ms')
                    ->label('زمان پاسخ')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : "{$state} ms")
                    ->sortable(),
            ]);
    }
}
