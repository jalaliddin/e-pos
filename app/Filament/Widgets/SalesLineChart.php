<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesLineChart extends ChartWidget
{
    protected static ?string $heading = 'Oylik savdo dinamikasi';
    protected static ?string $description = 'Joriy yil bo\'yicha oylik daromad va foyda';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'year'     => 'Bu yil',
            'last12'   => 'Oxirgi 12 oy',
            'last6'    => 'Oxirgi 6 oy',
        ];
    }

    protected function getData(): array
    {
        $symbol = config('settings.currency_symbol', '$');

        [$labels, $revenue, $profit] = match ($this->filter) {
            'last6'  => $this->buildMonthly(6),
            'last12' => $this->buildMonthly(12),
            default  => $this->buildCurrentYear(),
        };

        return [
            'datasets' => [
                [
                    'label'                => 'Daromad',
                    'data'                 => $revenue,
                    'borderColor'          => 'rgba(59,130,246,1)',
                    'backgroundColor'      => 'rgba(59,130,246,0.08)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => 'rgba(59,130,246,1)',
                    'pointRadius'          => 4,
                    'borderWidth'          => 2,
                ],
                [
                    'label'                => 'Foyda',
                    'data'                 => $profit,
                    'borderColor'          => 'rgba(34,197,94,1)',
                    'backgroundColor'      => 'rgba(34,197,94,0.08)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => 'rgba(34,197,94,1)',
                    'pointRadius'          => 4,
                    'borderWidth'          => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function buildCurrentYear(): array
    {
        $year = now()->year;
        $months = collect(range(1, 12));

        $orders = Order::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as rev, SUM(income_price) as cost')
            ->groupBy('month')
            ->pluck('rev', 'month')
            ->toArray();

        $costs = Order::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(income_price) as cost')
            ->groupBy('month')
            ->pluck('cost', 'month')
            ->toArray();

        $labels  = $months->map(fn($m) => Carbon::create($year, $m)->translatedFormat('M'))->toArray();
        $revenue = $months->map(fn($m) => (int)($orders[$m] ?? 0))->toArray();
        $profit  = $months->map(fn($m) => max(0, (int)(($orders[$m] ?? 0) - ($costs[$m] ?? 0))))->toArray();

        return [$labels, $revenue, $profit];
    }

    private function buildMonthly(int $count): array
    {
        $months = collect(range($count - 1, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());

        $orders = Order::where('created_at', '>=', now()->subMonths($count)->startOfMonth())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, SUM(total_price) as rev, SUM(income_price) as cost')
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $labels  = $months->map(fn($m) => $m->translatedFormat('M y'))->toArray();
        $revenue = $months->map(fn($m) => (int)($orders[$m->format('Y-m')]->rev ?? 0))->toArray();
        $profit  = $months->map(fn($m) => max(0, (int)(
            ($orders[$m->format('Y-m')]->rev ?? 0) - ($orders[$m->format('Y-m')]->cost ?? 0)
        )))->toArray();

        return [$labels, $revenue, $profit];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        $symbol = config('settings.currency_symbol', '$');

        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'top',
                    'labels'   => ['usePointStyle' => true, 'padding' => 16],
                ],
                'tooltip' => [
                    'callbacks' => [],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(156,163,175,0.15)'],
                    'ticks' => ['maxTicksLimit' => 6],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
            'interaction' => [
                'mode'      => 'index',
                'intersect' => false,
            ],
        ];
    }
}
