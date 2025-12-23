<?php

namespace App\Filament\Resources\LaporanBukuBesarResource\Pages;

use App\Filament\Resources\LaporanBukuBesarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanBukuBesars extends ListRecords
{
    protected static string $resource = LaporanBukuBesarResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
