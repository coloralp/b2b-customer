<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Filament\Resources\AccountResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class AccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = 'My Account';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {

        $options = collect(User::CUSTOMER_OPTIONS)->filter(fn($item) => ($item != 'email' and $item != 'password'))->toArray();

        $optionsForms = [];

//        foreach ($options as $option) {
//            $optionsForms[] =
//                Forms\Components\TextInput::make($option)->type('text')
//                    ->default(function (?User $user) use ($option) {
//                        return $user?->getOption($option, RoleEnum::CUSTOMER->value)->value;
//                    })->label(__('customer-edit.' . $option));
//        }
        return $form
//            ->schema(array_merge(
////                [
//                Forms\Components\TextInput::make('email')->label('Email'),
////                ],
//                [
//                    Forms\Components\Section::make('Change Password')
//                        ->description('For change password')
//                        ->schema([
//                            Forms\Components\TextInput::make('password')->label('Password')->password()->autocomplete(false),
//                            Forms\Components\TextInput::make('repeat_password')->label('Repeat Password')->password(),
//
//                        ])
//                ]));

            ->schema(
//
                [
                    Forms\Components\TextInput::make('email')
                        ->columnSpan('full')->readOnly()
                        ->label('Email')->columns(12),
                    Forms\Components\Section::make('Change Password')
                        ->description('For change password')
                        ->schema([
                            Forms\Components\TextInput::make('password')->label('Password')->password()
                                ->autocomplete(false)
                                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                ->rules(['min:8'])
                                ->reactive(),
                            Forms\Components\TextInput::make('password_confirmation')->label('Repeat Password')
                                ->password()
                                ->same('password')
                                ->requiredWith('password'),

                        ])
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            ])
            ->filters([
                //
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
//            'index' => Pages\ListAccounts::route('/'),
            'index' => Pages\EditAccount::route(''),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('id', auth()->id());
    }
}
