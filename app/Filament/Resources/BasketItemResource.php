<?php

namespace App\Filament\Resources;

use App\Enums\CurrencySymbol;
use App\Filament\Resources\BasketItemResource\Pages;

use App\Models\BasketItem;
use App\Models\Game;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class BasketItemResource extends Resource
{
    protected static ?string $model = BasketItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('game_id')
                    ->required()
                    ->relationship('game', 'name')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => Game::where('name', 'like', "%{$search}%")->getAvailAble()->limit(10)->pluck('name', 'id')->toArray())
                    ->searchable(['name'])
                    ->live(),

                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->maxValue(function (Forms\Get $get) {
                        $gameId = $get('game_id');
                        return Game::find($gameId)->stock ?? 100;
                    })->minValue(1)->default(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')->sortable()->copyable(),
                Tables\Columns\TextInputColumn::make('qty')->type('number')->updateStateUsing(function (BasketItem $record, $state) {
                    $stock = $record->game->stock;
                    if ($state > $stock) {
                        $message = "Stock is {$stock} for {$record->game->name}";
                        Notification::make()->title('Stock Error')->danger()
                            ->body($message)
                            ->send();
                        return $stock;
                    }
                }),
                Tables\Columns\TextColumn::make('unit_price')->numeric()
                    ->money(CurrencySymbol::EUR->name)->label('Unit Price'),//set max,
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasketItems::route('/'),
            'create' => Pages\CreateBasketItem::route('/create'),
            'edit' => Pages\EditBasketItem::route('/{record}/edit'),
        ];
    }

}
