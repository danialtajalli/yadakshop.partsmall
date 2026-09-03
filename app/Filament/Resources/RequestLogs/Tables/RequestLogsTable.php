<?php

namespace App\Filament\Resources\RequestLogs\Tables;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RequestLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('11s')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('زمان رخداد')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(),
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
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('method')
                    ->label('متد')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status_code')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        $state >= 300 => 'info',
                        $state >= 200 => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_reportable_status')
                    ->label('گزارش‌پذیر')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('duration_ms')
                    ->label('زمان پاسخ')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : "{$state} ms")
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('path')
                    ->label('مسیر')
                    ->searchable()
                    ->copyable()
                    ->limit(70)
                    ->tooltip(fn ($record): string => $record->path)
                    ->toggleable(),
                TextColumn::make('route_name')
                    ->label('روت')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip')
                    ->label('آی‌پی')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user_agent')
                    ->label('عامل کاربر')
                    ->searchable()
                    ->copyable()
                    ->limit(90)
                    ->tooltip(fn ($record): ?string => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_id')
                    ->label('شناسه کاربر')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->label('آدرس کامل')
                    ->searchable()
                    ->copyable()
                    ->limit(80)
                    ->tooltip(fn ($record): string => $record->url)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referer')
                    ->label('ارجاع‌دهنده')
                    ->searchable()
                    ->copyable()
                    ->limit(80)
                    ->tooltip(fn ($record): ?string => $record->referer)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('exception')
                    ->label('خطا')
                    ->searchable()
                    ->limit(90)
                    ->wrap()
                    ->tooltip(fn ($record): ?string => $record->exception)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('query')
                    ->label('کوئری')
                    ->formatStateUsing(fn (mixed $state): string => blank($state) ? '-' : json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
                    ->limit(80)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('ip')
                    ->label('آی‌پی')
                    ->schema([
                        TextInput::make('value')
                            ->label('آی‌پی')
                            ->placeholder('مثال: 192.168.1.1'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->where('ip', $data['value']),
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        if (blank($data['value'] ?? null)) {
                            return [];
                        }

                        return [
                            Indicator::make('آی‌پی: '.$data['value'])
                                ->removeField('value'),
                        ];
                    }),
                SelectFilter::make('log_type')
                    ->label('نوع')
                    ->options([
                        'incoming_request' => 'درخواست',
                        'reportable_response' => 'پاسخ گزارش‌پذیر',
                        'reportable_exception' => 'خطا',
                    ]),
                SelectFilter::make('status_family')
                    ->label('خانواده وضعیت')
                    ->options([
                        200 => '2xx',
                        300 => '3xx',
                        400 => '4xx',
                        500 => '5xx',
                    ]),
                SelectFilter::make('is_reportable_status')
                    ->label('گزارش‌پذیر')
                    ->options([
                        1 => 'بله',
                        0 => 'خیر',
                    ]),
            ]);
    }
}
