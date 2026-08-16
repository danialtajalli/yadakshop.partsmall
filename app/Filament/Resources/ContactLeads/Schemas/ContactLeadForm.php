<?php

namespace App\Filament\Resources\ContactLeads\Schemas;

use App\Models\ContactLead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات تماس')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('نام')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('phone')
                            ->label('شماره موبایل')
                            ->required()
                            ->maxLength(15),
                        Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                ContactLead::STATUS_PENDING => 'در انتظار',
                                ContactLead::STATUS_COMPLETED => 'تکمیل شده',
                                ContactLead::STATUS_FAILED => 'ناموفق',
                            ])
                            ->required(),
                        Textarea::make('message')
                            ->label('پیام')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make('اطلاعات دیدار')
                    ->columns(2)
                    ->schema([
                        TextInput::make('pipeline')
                            ->label('مسیر ثبت')
                            ->required()
                            ->maxLength(64),
                        TextInput::make('didar_person_id')
                            ->label('شناسه مخاطب دیدار')
                            ->maxLength(255),
                        TextInput::make('didar_deal_id')
                            ->label('شناسه معامله دیدار')
                            ->maxLength(255),
                        TextInput::make('didar_product_id')
                            ->label('شناسه محصول دیدار')
                            ->maxLength(255),
                        TextInput::make('didar_owner_id')
                            ->label('شناسه مالک دیدار')
                            ->maxLength(255),
                        TextInput::make('didar_pipeline_id')
                            ->label('شناسه پایپ‌لاین دیدار')
                            ->maxLength(255),
                        TextInput::make('didar_pipeline_stage_id')
                            ->label('شناسه مرحله دیدار')
                            ->maxLength(255),
                        Textarea::make('failure_reason')
                            ->label('دلیل خطا')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
