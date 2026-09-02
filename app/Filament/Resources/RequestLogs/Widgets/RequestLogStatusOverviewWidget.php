<?php

namespace App\Filament\Resources\RequestLogs\Widgets;

use App\Models\RequestLog;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RequestLogStatusOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'خلاصه وضعیت درخواست‌ها';

    protected ?string $description = 'بر اساس بازه زمانی انتخاب شده';

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
        $range = $this->validRange($this->pageFilters['range'] ?? null);
        $from = $this->fromRange($range);

        $baseQuery = RequestLog::query()
            ->where('occurred_at', '>=', $from);

        $total = (clone $baseQuery)->count();
        $ok = (clone $baseQuery)->where('status_code', 200)->count();
        $notOk = (clone $baseQuery)
            ->where(fn (Builder $query): Builder => $query
                ->where('status_code', '!=', 200)
                ->orWhereNull('status_code')
                ->orWhere('log_type', 'reportable_exception'))
            ->count();
        $clientErrors = (clone $baseQuery)->whereBetween('status_code', [400, 499])->count();
        $serverErrors = (clone $baseQuery)->whereBetween('status_code', [500, 599])->count();
        $exceptions = (clone $baseQuery)->where('log_type', 'reportable_exception')->count();

        return [
            Stat::make('کل درخواست‌ها', number_format($total))
                ->description($this->rangeLabel($range))
                ->icon(Heroicon::OutlinedChartBar)
                ->color('primary'),
            Stat::make('وضعیت ۲۰۰', number_format($ok))
                ->description($total > 0 ? number_format(($ok / $total) * 100, 1).'٪ از کل' : 'بدون داده')
                ->descriptionColor('success')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
            Stat::make('غیر ۲۰۰ / خطاها', number_format($notOk))
                ->description($notOk > 0 ? 'نیازمند بررسی' : 'بدون مورد')
                ->descriptionColor($notOk > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color($notOk > 0 ? 'warning' : 'success'),
            Stat::make('خطاهای ۴xx', number_format($clientErrors))
                ->description('خطاهای سمت درخواست')
                ->icon(Heroicon::OutlinedExclamationCircle)
                ->color($clientErrors > 0 ? 'warning' : 'gray'),
            Stat::make('خطاهای ۵xx', number_format($serverErrors))
                ->description('خطاهای سمت سرور')
                ->descriptionColor($serverErrors > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedXCircle)
                ->color($serverErrors > 0 ? 'danger' : 'success'),
            Stat::make('استثناها', number_format($exceptions))
                ->description('ثبت شده توسط گزارشگر خطا')
                ->descriptionColor($exceptions > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedClock)
                ->color($exceptions > 0 ? 'danger' : 'gray'),
        ];
    }

    private function validRange(mixed $range): string
    {
        return in_array($range, ['10m', '1h', '1d', '1w', '1mo'], true) ? $range : '10m';
    }

    private function fromRange(string $range): CarbonImmutable
    {
        $now = CarbonImmutable::now();

        return match ($range) {
            '1h' => $now->subHour(),
            '1d' => $now->subDay(),
            '1w' => $now->subWeek(),
            '1mo' => $now->subMonth(),
            default => $now->subMinutes(10),
        };
    }

    private function rangeLabel(string $range): string
    {
        return match ($range) {
            '1h' => 'در ۱ ساعت اخیر',
            '1d' => 'در ۱ روز اخیر',
            '1w' => 'در ۱ هفته اخیر',
            '1mo' => 'در ۱ ماه اخیر',
            default => 'در ۱۰ دقیقه اخیر',
        };
    }
}
