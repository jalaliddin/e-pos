<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currency_symbol = config('settings.currency_symbol');

        $totalOrdersLast30Days = Order::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $totalIncomeLast30Days = Order::where('created_at', '>=', Carbon::now()->subDays(30))->sum('total_price');
        $totalcustomersLast30Days = Customer::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        return [
            Stat::make('Buyurtmalar soni', $totalOrdersLast30Days)
                    ->description("Oxirgi 30 kun davomida")
                    ->descriptionIcon('heroicon-o-inbox-stack', IconPosition::Before)
                    ->chart([1,5,10,50])
                    ->color('success'),

            Stat::make('Kirimlar', $currency_symbol.$totalIncomeLast30Days)
                    ->description("Oxirgi 30 kun davomidagi kirimlar")
                    ->descriptionIcon('heroicon-o-banknotes', IconPosition::Before)
                    ->chart([1,5,30, 50])
                    ->color('success'),
            
            Stat::make('Mijozlar soni', $totalcustomersLast30Days)
                    ->description("Oxirgi 30 kun davomidagi mijozlar")
                    ->descriptionIcon('heroicon-o-user-group', IconPosition::Before)
                    ->chart([1,5,15, 25])
                    ->color('success'),       
        ];
    }
}
