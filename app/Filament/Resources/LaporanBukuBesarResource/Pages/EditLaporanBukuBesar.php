<?php

namespace App\Filament\Resources\LaporanBukuBesarResource\Pages;

use App\Filament\Resources\LaporanBukuBesarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanBukuBesar extends EditRecord
{
    protected static string $resource = LaporanBukuBesarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
