<?php

namespace App\Filament\Resources;

use App\Enums\CurrencyEnum;
use App\Enums\JarTransactionRequestEnum;
use App\Filament\Resources\JarTransactionsRequestResource\Pages;
use App\Models\Currency;
use App\Models\JarTransactionsRequest;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;

class JarTransactionsRequestResource extends Resource
{
    protected static ?string $model = JarTransactionsRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Transaction Requests';

    protected static ?string $navigationGroup = 'Moneybox';

    protected static ?string $breadcrumb = 'Transaction Request';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')->required()->numeric(),
                Forms\Components\Select::make('amount_currency')->label('Currency')
                    ->options(Currency::all()->pluck('name', 'id')->toArray())
                    ->default(CurrencyEnum::EUR->value),
                Forms\Components\Textarea::make('description')->maxLength(65535)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('who.name')->searchable()->label('Created By'),
                Tables\Columns\TextColumn::make('created_at')->sortable()->label('Created At'),
                Tables\Columns\TextColumn::make('amount_jar_front')->label('Amount'),
                Tables\Columns\TextColumn::make('status')->badge()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(JarTransactionRequestEnum::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('amount_currency')
                    ->options(CurrencyEnum::class)
                    ->multiple(),

                Filter::make('amount')
                    ->form([
                        TextInput::make('amount_less')->type('range')->label('Amount Less'),
                        TextInput::make('amount_max')->type('range')->label('Amount Max')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_less'],
                                fn(Builder $query, $date): Builder => $query->where('amount', '>=', $date),
                            )->when(
                                $data['amount_max'],
                                fn(Builder $query, $date): Builder => $query->where('amount', '<=', $date),
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
                Tables\Actions\EditAction::make()->hidden(fn(JarTransactionsRequest $record) => ($record->status->value != JarTransactionRequestEnum::CREATED->value)),
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
            'index' => Pages\ListJarTransactionsRequests::route('/'),
            'create' => Pages\CreateJarTransactionsRequest::route('/create'),
            'edit' => Pages\EditJarTransactionsRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderByDesc('created_at')->where('jar_id', auth()->user()->jar->id);
    }
}
