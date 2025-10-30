<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationLabel = 'Sotilgan mahsulotlar';

    protected static ?string $pluralLabel = 'Sotilgan mahsulotlar';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('income_price')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Buyurtma raqami')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Soni')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Narxi')
                    ->money()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $currency_symbol.number_format($record->income_price, 0, ',', ' '))
                    ->summarize(
                        Sum::make()
                            ->label('Jami')
                            ->query(fn ($query) => $query)
                            ->formatStateUsing(fn ($state) => $currency_symbol.number_format($state, 0, ',', ' '))
                    ),
                // Tables\Columns\TextColumn::make('tax')
                // ->label('QQS')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan sana')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('income_price')
                    ->label('Asl narxi')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $currency_symbol.number_format($record->income_price, 0, ',', ' '))
                    ->summarize(
                        Sum::make()
                            ->label('Jami')
                            ->query(fn ($query) => $query)
                            ->formatStateUsing(fn ($state) => $currency_symbol.number_format($state, 0, ',', ' '))
                    ),
                TextColumn::make('profit')
                    ->label('Foyda')
                    ->state(fn ($record) => $record->total_price - $record->income_price)
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ').' so‘m')
                    ->summarize([
                        Summarizer::make()
                            ->label('Foyda jami')
                            ->using(function ($query) {
                                // Bu yerda barcha filterlar hisobga olinadi
                                $total = $query->sum('price');
                                $income = $query->sum('income_price');

                                return $total - $income;
                            })
                            ->formatStateUsing(fn ($state) => '🟢 '.number_format($state, 0, ',', ' ').' so‘m'
                            ),
                    ]),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Kategoriya')
                    ->searchable(),
            ])
            ->filters([
SelectFilter::make('category_name')
    ->label('Kategoriya')
    ->options(
        \App\Models\OrderItem::query()
            ->select('category_name')
            ->distinct()
            ->pluck('category_name', 'category_name')
    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListOrderItems::route('/'),
            // 'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
