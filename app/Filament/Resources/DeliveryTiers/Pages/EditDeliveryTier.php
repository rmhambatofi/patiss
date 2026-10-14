<?php

namespace App\Filament\Resources\DeliveryTiers\Pages;

use App\Filament\Resources\DeliveryTiers\DeliveryTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryTier extends EditRecord
{
    protected static string $resource = DeliveryTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
