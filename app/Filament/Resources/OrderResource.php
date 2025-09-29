<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $pluralLabel = 'Buyurtmalar';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Buyurtmalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('customer.first_name')
                    ->label('Mijoz ismi')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->customer->first_name.' '.$record->customer->last_name),
                TextColumn::make('total_price')
                    ->label('Umumiy narxi')
                    ->formatStateUsing(fn ($record) => $currency_symbol.$record->total_price)->sortable(),
                TextColumn::make('created_at')->sortable()->dateTime()->label('Yaratilgan sana'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Dan'),
                        DatePicker::make('end_date')
                            ->label('Gacha'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['end_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data) {
                        $indicators = [];

                        if (! empty($data['start_date'])) {
                            $indicators[] = 'Dan: '.$data['start_date'];
                        }

                        if (! empty($data['end_date'])) {
                            $indicators[] = 'Gacha: '.$data['end_date'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn ($resource) => $resource::getModelLabel().'-'.date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                            ->withColumns([
                                Column::make('customer.phone')->heading('Telefon'),
                                Column::make('customer.email')->heading('Email'),
                                Column::make('customer.address')->heading('Manzil'),
                                Column::make('updated_at'),
                            ]),
                    ]),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
