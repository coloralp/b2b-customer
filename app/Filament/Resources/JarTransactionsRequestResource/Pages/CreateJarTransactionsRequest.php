<?php

namespace App\Filament\Resources\JarTransactionsRequestResource\Pages;

use App\Enums\JarTransactionEnum;
use App\Filament\Resources\JarTransactionsRequestResource;
use App\Models\BasketItem;
use App\Services\JarTransactionService;
use App\Services\Panel\JarTransactionRequestService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateJarTransactionsRequest extends CreateRecord
{
    protected static string $resource = JarTransactionsRequestResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        $jarTransactionRequestService = new JarTransactionRequestService();


        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record['processed_by'] = auth()->id();
        $record['jar_id'] = auth()->user()->jar->id;
        $record['jar_transaction_type'] = JarTransactionEnum::INCOME->value;


        $id = $jarTransactionRequestService->createTransaction($record->toArray());

        $record['id'] = $id;
        
        return $record;

    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
