<?php

namespace App\Filament\Resources\DeliveryTiers\Pages;

use App\Filament\Resources\DeliveryTiers\DeliveryTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryTiers extends ListRecords
{
    protected static string $resource = DeliveryTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
