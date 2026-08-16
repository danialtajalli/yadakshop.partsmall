<?php

namespace App\Filament\Resources\ContactLeads\Tables;

use App\Models\ContactLead;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')
                    ->label('نام')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_name')
                    ->label('نام خانوادگی')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('شماره موبایل')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('message')
                    ->label('پیام')
                    ->limit(60)
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        ContactLead::STATUS_PENDING => 'در انتظار',
                        ContactLead::STATUS_COMPLETED => 'تکمیل شده',
                        ContactLead::STATUS_FAILED => 'ناموفق',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        ContactLead::STATUS_COMPLETED => 'success',
                        ContactLead::STATUS_FAILED => 'danger',
                        default => 'warning',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('pipeline')
                    ->label('مسیر ثبت')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('didar_deal_id')
                    ->label('شناسه معامله دیدار')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('failure_reason')
                    ->label('دلیل خطا')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        ContactLead::STATUS_PENDING => 'در انتظار',
                        ContactLead::STATUS_COMPLETED => 'تکمیل شده',
                        ContactLead::STATUS_FAILED => 'ناموفق',
                    ]),
                SelectFilter::make('pipeline')
                    ->label('مسیر ثبت')
                    ->options([
                        'didar' => 'دیدار',
                        'didar_with_database' => 'دیدار و دیتابیس',
                        'database_only' => 'فقط دیتابیس',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('ویرایش'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }
}
