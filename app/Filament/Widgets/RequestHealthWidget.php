<?php

namespace App\Filament\Widgets;

use App\Models\RequestLog;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class RequestHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'سلامت درخواست‌ها';

    protected ?string $description = 'بر اساس لاگ‌های ثبت شده از کل سیستم';

    protected ?string $pollingInterval = '11s';

    public static function canView(): bool
    {
        return Schema::hasTable('request_logs');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $now = CarbonImmutable::now();
        $lastTenMinutes = $now->subMinutes(10);
        $lastHour = $now->subHour();

        $lastTenMinuteQuery = RequestLog::query()
            ->where('occurred_at', '>=', $lastTenMinutes);

        $lastHourQuery = RequestLog::query()
            ->where('occurred_at', '>=', $lastHour);

        $requestsLastTenMinutes = (clone $lastTenMinuteQuery)->count();
        $errorsLastTenMinutes = (clone $lastTenMinuteQuery)
            ->where(fn ($query) => $query
                ->where('is_reportable_status', true)
                ->orWhere('log_type', 'reportable_exception'))
            ->count();

        $errorsLastHour = (clone $lastHourQuery)
            ->where(fn ($query) => $query
                ->where('is_reportable_status', true)
                ->orWhere('log_type', 'reportable_exception'))
            ->count();

        $averageDuration = (clone $lastTenMinuteQuery)
            ->whereNotNull('duration_ms')
            ->avg('duration_ms');

        return [
            Stat::make('درخواست‌های ۱۰ دقیقه اخیر', number_format($requestsLastTenMinutes))
                ->description('رفرش خودکار هر ۱۱ ثانیه')
                ->icon(Heroicon::OutlinedSignal)
                ->color('primary'),
            Stat::make('خطاهای ۱۰ دقیقه اخیر', number_format($errorsLastTenMinutes))
                ->description($errorsLastTenMinutes > 0 ? 'نیازمند بررسی' : 'بدون خطای گزارش‌پذیر')
                ->descriptionColor($errorsLastTenMinutes > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color($errorsLastTenMinutes > 0 ? 'danger' : 'success'),
            Stat::make('خطاهای یک ساعت اخیر', number_format($errorsLastHour))
                ->description('وضعیت کلی لاگ‌های اخیر')
                ->descriptionColor($errorsLastHour > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedChartBar)
                ->color($errorsLastHour > 0 ? 'warning' : 'success'),
            Stat::make('میانگین زمان پاسخ', $averageDuration === null ? '-' : number_format((float) $averageDuration).' ms')
                ->description('برای درخواست‌های ۱۰ دقیقه اخیر')
                ->icon(Heroicon::OutlinedClock)
                ->color($averageDuration !== null && $averageDuration > 1000 ? 'warning' : 'success'),
        ];
    }
}
