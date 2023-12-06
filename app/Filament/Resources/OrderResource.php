<?php

namespace App\Filament\Resources;

use App\Enums\CurrencySymbol;
use App\Enums\JarTransactionRequestEnum;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Game;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'order_code';

    protected static ?string $breadcrumb = 'My Orders';




    /**
     * @return string|null
     */

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Placeholder::make('Total')
//                    ->content(fn(?Model $record) => $record->total_amount . CurrencySymbol::EUR->value),
//                Placeholder::make('Key Count')
//                    ->content(fn(?Model $record) => $record->orderItems->sum('quantity')),
                Forms\Components\Repeater::make('orderItems')
                    ->schema([
                        Forms\Components\Select::make('game_id')
                            ->required()
                            ->relationship('game', 'name')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search): array => Game::where('name', 'like', "%{$search}%")
                                ->getAvailAble()->limit(10)->select(['id', 'name', 'stock'])->pluck('name', 'id')->toArray())
                            ->searchable(['name'])
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $game = Game::find($state);
                                if ($game) {
                                    $set('unit_price', $game->amount);
                                }
                            })
                            ->live(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->maxValue(function (Forms\Get $get) {
                                $gameId = $get('game_id');
                                return Game::find($gameId)->stock ?? 10;
                            })->minValue(1)
                            ->default(1)
                            ->label('Quantity')
                            ->disabled(fn(Forms\Get $get) => is_null($get('game_id'))),//game_id seçilmeden yapmak yok
                        Forms\Components\TextInput::make('unit_price')
                            ->readOnly()
                            ->numeric()
                            ->label('Unit Price'),
                    ])->minItems(1)->defaultItems(1)->columnSpan('full')->collapsible()->columns(3)->hidden(function (string $operation) {
                        return $operation == 'edit';
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_type')->badge(),
                Tables\Columns\TextColumn::make('order_code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Creator'),
                Tables\Columns\TextColumn::make('total_amount')->money(CurrencySymbol::EUR->name)->label('Price'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i:s ')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                Filter::make('who')
                    ->form([
                        Forms\Components\Toggle::make('by_me')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['by_me'],
                                fn(Builder $query, $date): Builder => $query->where('who', auth()->id()),
                            );
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        OrderStatus::CREATED->value => OrderStatus::CREATED->name,
                        OrderStatus::APPROVE->value => OrderStatus::APPROVE->name,
                        OrderStatus::REJECT->value => OrderStatus::REJECT->name,
                    ])
                    ->multiple(),
                Filter::make('total_amount')
                    ->form([
                        TextInput::make('amount_less')->type('range')->label('Amount Less'),
                        TextInput::make('amount_max')->type('range')->label('Amount Max')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_less'],
                                fn(Builder $query, $date): Builder => $query->where('total_amount', '>=', $date),
                            )->when(
                                $data['amount_max'],
                                fn(Builder $query, $date): Builder => $query->where('total_amount', '<=', $date),
                            );
                    }),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d H::i:s')
                            ->reactive(),
                        DatePicker::make('until')
                            ->displayFormat('d/m/Y')
                            ->reactive()
                            ->native(false)
                            ->minDate(fn(Forms\Get $get) => Carbon::parse($get('from'))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Order $order) => $order->status->value == OrderStatus::APPROVE->value),
//                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('order_type', OrderTypeEnum::FROM_CUSTOMER_PANEL->value)
            ->where('who', auth()->id());
    }
}
