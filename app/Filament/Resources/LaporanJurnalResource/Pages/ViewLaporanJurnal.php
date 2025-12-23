<?php

namespace App\Filament\Resources\LaporanJurnalResource\Pages;

use App\Filament\Resources\LaporanJurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanJurnal extends ViewRecord
{
    protected static string $resource = LaporanJurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
