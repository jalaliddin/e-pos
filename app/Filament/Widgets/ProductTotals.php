<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductTotals extends BaseWidget
{
    protected function getStats(): array
    {
        $currencySymbol = config('settings.currency_symbol');

        $totals = Product::query()
            ->where('status', true)
            ->selectRaw('COALESCE(SUM(quantity * income_price), 0) as total_income_price')
            ->selectRaw('COALESCE(SUM(quantity * price), 0) as total_price')
            ->first();

        $totalIncomePrice = (float) ($totals->total_income_price ?? 0);
        $totalPrice = (float) ($totals->total_price ?? 0);

        return [
            Stat::make('Barcha mahsulotlar kirim narxi', number_format($totalIncomePrice, 0, ',', ' ') . $currencySymbol)
                ->description('Faol mahsulotlar bo\'yicha jami kirim qiymati')
                ->descriptionIcon('heroicon-o-banknotes', IconPosition::Before)
                ->color('success'),

            Stat::make('Barcha mahsulotlar umumiy narxi', number_format($totalPrice, 0, ',', ' ') . $currencySymbol)
                ->description('Faol mahsulotlar bo\'yicha jami sotuv qiymati')
                ->descriptionIcon('heroicon-o-chart-bar', IconPosition::Before)
                ->color('primary'),
        ];
    }
}

