<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $symbol = config('settings.currency_symbol', '$');
        $fmt    = fn($n) => $symbol . number_format($n, 0, ',', ' ');

        $now   = Carbon::now();
        $from  = $now->copy()->startOfMonth();
        $pFrom = $now->copy()->subMonth()->startOfMonth();
        $pTo   = $now->copy()->subMonth()->endOfMonth();

        // Bu oy
        $revenue = Order::where('created_at', '>=', $from)->sum('total_price');
        $profit  = OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', $from))
            ->selectRaw('SUM((price - income_price) * quantity) as t')->value('t') ?? 0;
        $orders  = Order::where('created_at', '>=', $from)->count();
        $customers = Customer::where('created_at', '>=', $from)->count();

        // O'tgan oy
        $prevRevenue = Order::whereBetween('created_at', [$pFrom, $pTo])->sum('total_price');
        $prevOrders  = Order::whereBetween('created_at', [$pFrom, $pTo])->count();

        // Kunlik chart uchun (30 kun)
        $dailyRevenue = Order::where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')->orderBy('date')->pluck('total')->toArray();

        $dailyOrders = Order::where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')->orderBy('date')->pluck('total')->toArray();

        $revenueChange = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $ordersChange = $prevOrders > 0
            ? round((($orders - $prevOrders) / $prevOrders) * 100, 1)
            : 0;

        $revenueDesc = ($revenueChange >= 0 ? '+' : '') . $revenueChange . '% o\'tgan oyga nisbatan';
        $ordersDesc  = ($ordersChange  >= 0 ? '+' : '') . $ordersChange  . '% o\'tgan oyga nisbatan';

        return [
            Stat::make('Bu oy savdo', $fmt($revenue))
                ->description($revenueDesc)
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down', IconPosition::Before)
                ->chart(count($dailyRevenue) ? $dailyRevenue : [0])
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Bu oy foyda', $fmt($profit))
                ->description('Sotuv - kelish narxi')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
                ->chart(count($dailyRevenue) ? array_map(fn($r, $i) => max(0, $r * 0.2), $dailyRevenue, array_keys($dailyRevenue)) : [0])
                ->color('warning'),

            Stat::make('Bu oy buyurtmalar', number_format($orders))
                ->description($ordersDesc)
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down', IconPosition::Before)
                ->chart(count($dailyOrders) ? $dailyOrders : [0])
                ->color($ordersChange >= 0 ? 'success' : 'danger'),

            Stat::make('Bu oy yangi mijozlar', number_format($customers))
                ->description('Ro\'yxatdan o\'tgan mijozlar')
                ->descriptionIcon('heroicon-m-user-plus', IconPosition::Before)
                ->color('primary'),
        ];
    }
}
