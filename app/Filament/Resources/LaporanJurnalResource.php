<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanJurnalResource\Pages;
use App\Models\Jurnal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import Library Excel
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanJurnalResource extends Resource
{
    protected static ?string $model = Jurnal::class;

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
                
                // Tampilan di Website (Tetap pakai HTML biar cantik)
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
            ->headerActions([
                // --- TOMBOL EXPORT EXCEL ---
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Jurnal_Umum_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('tanggal')->heading('Tanggal'),
                                Column::make('keterangan')->heading('Keterangan'),
                                
                                // KHUSUS EXCEL: Kita format ulang datanya agar tidak ada tag HTML
                                Column::make('rincian_excel') // Nama dummy
                                    ->heading('Rincian Transaksi')
                                    ->formatStateUsing(function ($record) {
                                        // Kita loop manual datanya untuk Excel
                                        $lines = [];
                                        foreach ($record->detailJurnals as $detail) {
                                            $posisi = $detail->debit > 0 ? '(Debit)' : '(Kredit)';
                                            $nominal = $detail->debit > 0 ? $detail->debit : $detail->kredit;
                                            $akun = $detail->akun->nama_akun ?? '-';
                                            
                                            // Format: Kas (Debit): 50.000
                                            $lines[] = "{$akun} {$posisi}: " . number_format($nominal, 0, ',', '.');
                                        }
                                        // Gabungkan dengan "Enter" (\n) agar menjadi list di dalam satu sel Excel
                                        return implode("\n", $lines);
                                    }),
                            ]),
                    ]),
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