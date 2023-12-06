<?php

namespace App\Filament\Resources;

use App\Enums\CurrencyEnum;
use App\Enums\JarTransactionEnum;
use App\Filament\Resources\JarTransactionResource\Pages;
use App\Models\JarTransaction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;

class JarTransactionResource extends Resource
{
    protected static ?string $model = JarTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Transactions';

    protected static ?string $navigationGroup = 'Moneybox';

    protected static ?string $breadcrumb = 'Transactions';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\TextInput::make('code')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('processed_by')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('amount')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('amount_currency')
//                    ->required()
//                    ->numeric()
//                    ->default(0),
//                Forms\Components\TextInput::make('jar_transaction_type')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('old_balance')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('new_balance')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\Textarea::make('description')
//                    ->maxLength(65535)
//                    ->columnSpanFull(),
//                Forms\Components\Select::make('order_id')
//                    ->relationship('order', 'id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable()->label('Transaction Code'),
                Tables\Columns\TextColumn::make('who.name')->sortable()->label('Process By'),

                Tables\Columns\TextColumn::make('old_balance')->numeric()->sortable()->label('Old Balance'),

                Tables\Columns\TextColumn::make('process_amount_front')->numeric()->sortable()->label('Process Amount'),
                Tables\Columns\TextColumn::make('jar_transaction_type')->badge()->label('Process Type'),

                Tables\Columns\TextColumn::make('new_balance_front')->numeric()->sortable()->label('New Balence'),

                Tables\Columns\TextColumn::make('order_code_filament')->numeric()->label('Order Code'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i:s')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jar_transaction_type')
                    ->options(JarTransactionEnum::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('amount_currency')
                    ->options(CurrencyEnum::class)
                    ->multiple(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d H::i:s')
                            ->reactive(),
//                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('until', $state)),
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
                    }),

                Filter::make('amount')
                    ->form([
                        TextInput::make('amount_less')->type('range')->label('Amount Less'),
                        TextInput::make('amount_max')->type('range')->label('Amount Max')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_less'],
                                fn(Builder $query, $date): Builder => $query->whereDate('amount', '>=', $date),
                            )->when(
                                $data['amount_max'],
                                fn(Builder $query, $date): Builder => $query->whereDate('amount', '<=', $date),
                            );
                    })

            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJarTransactions::route('/'),
//            'create' => Pages\CreateJarTransaction::route('/create'),
//            'edit' => Pages\EditJarTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderByDesc('created_at')->where('jar_id', auth()->user()->jar->id);
    }
}
