<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Company;
use App\Models\ContactLead;
use App\Models\Part;
use App\Models\RepairShop;
use App\Models\Shop;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SystemOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected ?string $heading = 'مانیتورینگ کل سیستم';

    protected ?string $description = 'نمای کلی از موجودی و وضعیت داده‌های اصلی پنل';

    protected ?string $pollingInterval = '11s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $confirmedShops = Shop::query()->where('confirmed', true)->count();
        $totalShops = Shop::query()->count();
        $verifiedShops = Shop::query()->where('verified', true)->count();

        return [
            Stat::make('فروشگاه‌ها', number_format($totalShops))
                ->description(number_format($confirmedShops).' تایید شده، '.number_format($verifiedShops).' احراز شده')
                ->descriptionColor($confirmedShops === $totalShops ? 'success' : 'warning')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->color('primary'),
            Stat::make('تعمیرگاه‌ها', number_format(RepairShop::query()->count()))
                ->description('تعداد تعمیرگاه‌های ثبت شده')
                ->icon(Heroicon::OutlinedWrenchScrewdriver)
                ->color('info'),
            Stat::make('قطعات', number_format(Part::query()->count()))
                ->description('قطعات قابل نمایش در سایت')
                ->icon(Heroicon::OutlinedCube)
                ->color('success'),
            Stat::make('شرکت‌ها', number_format(Company::query()->count()))
                ->description('برندها و شرکت‌های ثبت شده')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('gray'),
            Stat::make('درخواست‌های تماس باز', number_format(ContactLead::query()->where('status', ContactLead::STATUS_PENDING)->count()))
                ->description(number_format(ContactLead::query()->where('status', ContactLead::STATUS_FAILED)->count()).' ناموفق')
                ->descriptionColor(ContactLead::query()->where('status', ContactLead::STATUS_FAILED)->exists() ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('warning'),
            Stat::make('نظرات در انتظار', number_format(Comment::query()->where('confirmed', false)->count()))
                ->description('نیازمند بررسی در پنل')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('warning'),
            Stat::make('صف پردازش', number_format(DB::table('jobs')->count()))
                ->description('Jobهای در انتظار اجرا')
                ->icon(Heroicon::OutlinedQueueList)
                ->color('info'),
            Stat::make('Jobهای ناموفق', number_format(DB::table('failed_jobs')->count()))
                ->description('نیازمند بررسی فنی')
                ->descriptionColor(DB::table('failed_jobs')->exists() ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationCircle)
                ->color(DB::table('failed_jobs')->exists() ? 'danger' : 'success'),
        ];
    }
}
