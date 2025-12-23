<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanJurnalResource\Pages;
use App\Models\Jurnal; // <--- PENTING: Arahkan ke Model Jurnal
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LaporanJurnalResource extends Resource
{
    protected static ?string $model = Jurnal::class; // Pakai Model Jurnal

    // Setting Nama Menu
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $slug = 'laporan-jurnal-umum';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
                
                // Tampilan Custom HTML Debit/Kredit
                Tables\Columns\TextColumn::make('detailJurnals')
                    ->label('Rincian Jurnal')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $html = '<ul class="text-xs space-y-1">';
                        foreach ($record->detailJurnals as $detail) {
                            $isDebit = $detail->debit > 0;
                            $color = $isDebit ? 'text-green-600 font-bold' : 'text-red-600';
                            $posisi = $isDebit ? '(D)' : '&nbsp;&nbsp;&nbsp;&nbsp;(K)';
                            $nominal = $isDebit ? $detail->debit : $detail->kredit;
                            $akun = $detail->akun->nama_akun ?? '-';
                            
                            $html .= "<li class='{$color}'>{$akun} {$posisi} Rp " . number_format($nominal, 0, ',', '.') . "</li>";
                        }
                        $html .= '</ul>';
                        return $html;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanJurnals::route('/'),
        ];
    }
}