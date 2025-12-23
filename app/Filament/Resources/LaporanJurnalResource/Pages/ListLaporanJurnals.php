<?php

namespace App\Filament\Resources\LaporanJurnalResource\Pages;

use App\Filament\Resources\LaporanJurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanJurnals extends ListRecords
{
    protected static string $resource = LaporanJurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
