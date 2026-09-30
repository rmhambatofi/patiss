<?php

namespace App\Filament\Resources\CapacityOverrides\Pages;

use App\Filament\Resources\CapacityOverrides\CapacityOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCapacityOverride extends EditRecord
{
    protected static string $resource = CapacityOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
