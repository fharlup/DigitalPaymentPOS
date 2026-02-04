<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Models\Transaksi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;

// Import Library Excel
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanPenjualanResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $modelLabel = 'Laporan Penjualan';
    protected static ?string $pluralModelLabel = 'Laporan Penjualan';
    protected static ?string $slug = 'laporan-penjualan';
    
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 5; // Urutan ke-5

    // PERBAIKAN 1: Hapus filter 'paid' disini agar SEMUA data muncul
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('created_at', 'desc'); // Pastikan yang terbaru paling atas
    }

    public static function table(Table $table): Table
    {
        return $table
            // PERBAIKAN 2: Auto Refresh setiap 5 detik (biar data baru langsung nongol)
            ->poll('5s')
            
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_meja')
                    ->label('Meja')
                    ->badge()->color('warning'),
                
                // Tambahkan Kolom Status biar ketahuan mana yang Belum Bayar
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Omzet')
                    ->money('IDR')
                    ->summarize(Sum::make()->label('Total')),
            ])
            ->filters([
                // PERBAIKAN 3: Filter Status dipindah kesini (Select)
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Belum Bayar',
                        'failed' => 'Gagal',
                    ])
                    ->label('Filter Status'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['sampai_tanggal'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    })
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Laporan')
                    ->color('success')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Laporan_Penjualan_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('created_at')->heading('Waktu Transaksi'),
                                Column::make('nama_pelanggan')->heading('Nama Pelanggan'),
                                Column::make('no_meja')->heading('Nomor Meja'),
                                Column::make('status')->heading('Status'),
                                Column::make('metode_pembayaran')->heading('Metode Bayar'),
                                Column::make('total_harga')
                                    ->heading('Omzet (Rp)')
                                    ->format('Rp #,##0'),
                            ]),
                    ]),
            ])
            ->actions([]) 
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPenjualans::route('/'),
        ];
    }
}