<?php

namespace App\Filament\Resources\LaporanJurnalResource\Pages;

use App\Filament\Resources\LaporanJurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanJurnal extends EditRecord
{
    protected static string $resource = LaporanJurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
