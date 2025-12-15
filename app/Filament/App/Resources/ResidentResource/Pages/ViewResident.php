<?php

namespace App\Filament\App\Resources\ResidentResource\Pages;

use App\Filament\App\Resources\ResidentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResident extends ViewRecord
{
    protected static string $resource = ResidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

