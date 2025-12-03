<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Milon\Barcode\DNS1D;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Mahsulotlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('barcode')
                    ->required()
                    ->default(rand(100, 10000))
                    ->unique(Product::class, 'barcode', ignoreRecord: true),
                TextInput::make('income_price')
                    ->numeric()
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->required(),
                TextInput::make('tax')
                    ->label('Tax (%)')
                    ->suffixIcon('heroicon-o-information-circle')
                    ->helperText('Example: 5 for 5% VAT/GST.')
                    ->numeric()
                    ->default(0.00),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required(),
                FileUpload::make('image')
                    ->disk('public_uploads')
                    ->panelLayout('grid')
                    ->visibility('public'),
                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                TextColumn::make('name')
                    ->width(250)
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')->disk('public_uploads')
                    ->size(50)
                    ->square(),
                TextColumn::make('barcode')->searchable(),
                TextInputColumn::make('quantity')->type('number')
                    ->sortable()
                    ->width(10)
                    ->rules(['required', 'integer', 'min:1']),
                TextColumn::make('price')->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('price')
                    ->label('Umumiy narxi')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $currency_symbol.number_format($record->price, 0, ',', ' '))
                    ->summarize(
                        Sum::make()
                            ->label('Jami')
                            ->query(fn ($query) => $query)
                            ->formatStateUsing(fn ($state) => $currency_symbol.number_format($state, 0, ',', ' '))
                    ),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategoriya')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Action::make('print_barcode')
                    ->label('Print Barcode')
                    ->icon('heroicon-o-printer')
                    ->url(fn($record) => route('barcode.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exports([
                    ExcelExport::make()
                        ->withFilename(fn($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                        ->withColumns([
                            Column::make('name')->heading('Name'),
                            Column::make('barcode')->heading('Barcode'),
                            Column::make('income_price')->heading('Original Price'),
                            Column::make('price')->heading('Price'),
                            Column::make('tax')->heading('Tax'),
                            Column::make('quantity')->heading('Quantity'),
                        ]),
                ]),
                BulkAction::make('print_barcodes')
                    ->label('Barcode chiqarish')
                    ->icon('heroicon-o-printer')
                    ->action(function (Collection $records, $data, $livewire) {
                        // Tanlangan ID'larni URL orqali yuboramiz
                        $ids = $records->pluck('id')->join(',');
                        return redirect()->route('barcode.bulk.print', ['ids' => $ids]);
                    }),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            // 'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
