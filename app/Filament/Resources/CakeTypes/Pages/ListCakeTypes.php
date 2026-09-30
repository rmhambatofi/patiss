<?php

namespace App\Filament\Resources\CakeTypes\Pages;

use App\Filament\Resources\CakeTypes\CakeTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCakeTypes extends ListRecords
{
    protected static string $resource = CakeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
