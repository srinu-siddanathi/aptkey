<?php

namespace App\Filament\App\Resources\ResidentResource\Pages;

use App\Filament\App\Resources\ResidentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResidents extends ListRecords
{
    protected static string $resource = ResidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

