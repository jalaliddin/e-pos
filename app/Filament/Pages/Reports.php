<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Hisobotlar';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.reports';

    public string $period = 'month';

    public function getTitle(): string { return 'Hisobotlar'; }

    public function setPeriod(string $period): void { $this->period = $period; }

    private function dateRange(): array
    {
        return match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year'  => [now()->startOfYear(), now()->endOfYear()],
            default => [null, null],
        };
    }

    private function prevDateRange(): array
    {
        return match ($this->period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'year'  => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [null, null],
        };
    }

    private function applyDate($q, $from, $to)
    {
        return $from ? $q->whereBetween('created_at', [$from, $to]) : $q;
    }

    private function applyDateViaOrder($q, $from, $to)
    {
        return $from
            ? $q->whereHas('order', fn($q2) => $q2->whereBetween('created_at', [$from, $to]))
            : $q;
    }

    public function getSummaryProperty(): array
    {
        [$from, $to]     = $this->dateRange();
        [$pFrom, $pTo]   = $this->prevDateRange();

        $cur  = $this->applyDate(Order::query(), $from, $to);
        $prev = $this->applyDate(Order::query(), $pFrom, $pTo);
        $curI = $this->applyDateViaOrder(OrderItem::query(), $from, $to);

        $revenue      = $cur->sum('total_price');
        $prevRevenue  = $prev->sum('total_price');
        $orders       = $this->applyDate(Order::query(), $from, $to)->count();
        $prevOrders   = $prev->count();
        $profit       = $curI->selectRaw('SUM((price - income_price) * quantity) as t')->value('t') ?? 0;
        $items        = $this->applyDateViaOrder(OrderItem::query(), $from, $to)->sum('quantity');
        $avgOrder     = $orders > 0 ? $revenue / $orders : 0;
        $margin       = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;

        $revChange = $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;
        $ordChange = $prevOrders  > 0 ? round((($orders  - $prevOrders)  / $prevOrders)  * 100, 1) : null;

        return compact('revenue', 'orders', 'profit', 'items', 'avgOrder', 'margin', 'revChange', 'ordChange');
    }

    public function getCategoryStatsProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->applyDateViaOrder(OrderItem::query(), $from, $to)
            ->selectRaw('category_name,
                SUM(quantity) as total_qty,
                SUM(price * quantity) as total_revenue,
                SUM((price - income_price) * quantity) as total_profit')
            ->groupBy('category_name')
            ->orderByDesc('total_profit')
            ->get();
    }

    public function getTopProductsProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->applyDateViaOrder(OrderItem::query(), $from, $to)
            ->selectRaw('name, product_id,
                SUM(quantity) as total_qty,
                SUM(price * quantity) as total_revenue,
                SUM((price - income_price) * quantity) as total_profit')
            ->groupBy('name', 'product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    public function getTopProfitProductsProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->applyDateViaOrder(OrderItem::query(), $from, $to)
            ->selectRaw('name, product_id,
                SUM(quantity) as total_qty,
                SUM((price - income_price) * quantity) as total_profit')
            ->groupBy('name', 'product_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();
    }

    public function getDailySalesProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->applyDate(Order::query(), $from, $to)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total_price) as revenue, SUM(income_price) as cost')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();
    }

    public function getHourlyStatsProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->applyDate(Order::query(), $from, $to)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders_count, SUM(total_price) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');
    }

    public function getLowStockProperty()
    {
        return Product::where('quantity', '<=', 5)->where('status', true)->orderBy('quantity')->limit(8)->get();
    }

    protected function getViewData(): array
    {
        return [
            'currency'          => config('settings.currency_symbol', '$'),
            'period'            => $this->period,
            'summary'           => $this->summary,
            'categoryStats'     => $this->categoryStats,
            'topProducts'       => $this->topProducts,
            'topProfitProducts' => $this->topProfitProducts,
            'dailySales'        => $this->dailySales,
            'hourlyStats'       => $this->hourlyStats,
            'lowStock'          => $this->lowStock,
        ];
    }
}
