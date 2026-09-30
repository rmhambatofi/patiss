<?php

namespace App\Filament\Resources\CapacityOverrides\Pages;

use App\Filament\Resources\CapacityOverrides\CapacityOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCapacityOverrides extends ListRecords
{
    protected static string $resource = CapacityOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
