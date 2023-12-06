<?php

namespace App\Filament\Resources;

use App\Enums\CurrencySymbol;
use App\Filament\Resources\GameResource\Pages;
use App\Models\BasketItem;
use App\Models\Category;
use App\Models\Game;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Game Name')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('region.name')->label('Region'),
                Tables\Columns\TextColumn::make('language.name')->label('Language'),
                Tables\Columns\TextColumn::make('stock')->label('Stock'),
                Tables\Columns\TextColumn::make('amount')->money(CurrencySymbol::EUR->name)->label('Price')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->label('Category')
                ,
                Tables\Filters\SelectFilter::make('region')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->label('Region'),
                Tables\Filters\SelectFilter::make('language')
                    ->relationship('language', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->label('Languages')
            ])
            ->actions([
                Tables\Actions\Action::make('GiveOrder')
                    ->form([
                        TextInput::make('qty')
                            ->numeric()
                            ->maxValue(fn(Model $record) => $record->stock)
                            ->default(function (Game $game) {
                                $item = BasketItem::whereGameId($game->id)->whereWho(auth()->id());
                                return $item->exists() ? $item->first()->qty : 0;
                            }),
                    ])
                    ->action(function (array $data, Game $game) {
                        BasketItem::upsert([
                            'game_id' => $game->id,
                            'unit_price' => $game->amount,
                            'who' => auth()->id(),
                            'qty' => $data['qty'] ?? 0
                        ], ['who', 'game_id']);
                    }),
            ])
            ->bulkActions([


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
            'index' => Pages\ListGames::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'region', 'language'])
            ->where('stock', '!=', 0)
            ->where('amount', '>', 0);
    }
}
