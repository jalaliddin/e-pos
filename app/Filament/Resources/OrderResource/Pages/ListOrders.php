<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use App\Models\OrderItem;


class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {

        return [
            Actions\CreateAction::make(),

            // ExportAction::make('export_order') 
            //         ->label('Order')

            // ->exports([
            //     ExcelExport::make()
            //         ->fromModel(Order::class)
            //         ->withFilename('order-' . date('Y-m-d'))
            //         ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            // ]),

            ExportAction::make('export_order') 
            ->label('Excel')
            ->exports(exports: [
                ExcelExport::make()
                    ->fromModel(Order::class)
                    ->withFilename('order-items-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ]),   
        ];
    }
        // 'name',
        // 'income_price',
        // 'price',
        // 'tax',
        // 'quantity',
        // 'product_id',
        // 'order_id'
    protected function getWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
}
