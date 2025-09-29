<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use App\Models\OrderItem;
use Filament\Actions;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListOrderItems extends ListRecords
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),

            ExportAction::make('export_order_items') 
            ->label('Excel')
            ->exports([
                ExcelExport::make()
                    ->fromModel(OrderItem::class)
                    ->withFilename('order-items-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ]),
        ];
    }
    
}
