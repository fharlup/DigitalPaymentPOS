<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Models\Transaksi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;

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
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('created_at', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_meja')
                    ->label('Meja')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        'done'    => 'info',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Omzet')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'paid'    => 'Lunas',
                        'pending' => 'Belum Bayar',
                        'failed'  => 'Gagal',
                        'done'    => 'Selesai',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['dari_tanggal'] ?? null),
                                fn ($q) => $q->whereDate('created_at', '>=', $data['dari_tanggal'])
                            )
                            ->when(
                                filled($data['sampai_tanggal'] ?? null),
                                fn ($q) => $q->whereDate('created_at', '<=', $data['sampai_tanggal'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (filled($data['dari_tanggal'] ?? null)) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d/m/Y');
                        }
                        if (filled($data['sampai_tanggal'] ?? null)) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)

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
                                Column::make('total_harga')->heading('Omzet (Rp)'),
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