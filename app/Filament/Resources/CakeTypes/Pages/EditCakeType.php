<?php

namespace App\Filament\Resources\CakeTypes\Pages;

use App\Filament\Resources\CakeTypes\CakeTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCakeType extends EditRecord
{
    protected static string $resource = CakeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
