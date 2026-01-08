<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBukuBesarResource\Pages;
use App\Models\DetailJurnal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms; // <--- PERBAIKAN 1: Tambahkan Import Ini
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;

// Import untuk Excel
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanBukuBesarResource extends Resource
{
    protected static ?string $model = DetailJurnal::class;

    protected static ?string $navigationLabel = 'Jurnal Umum'; 
    protected static ?string $slug = 'jurnal-umum';
    protected static ?string $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('jurnal.transaksi_id')
                    ->label('Nomor Transaksi')
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('jurnal.tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jurnal.transaksi_id')
                    ->label('Ref')
                    ->icon('heroicon-m-hashtag')
                    ->prefix('#')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Akun')
                    ->description(fn (DetailJurnal $record) => $record->akun->kode_akun ?? '-') 
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jurnal.keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(fn (DetailJurnal $record) => $record->jurnal->keterangan),
                
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->color('success') 
                    ->summarize(Sum::make()->label('Total Debit')),
                
                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->money('IDR')
                    ->color('danger') 
                    ->summarize(Sum::make()->label('Total Kredit')),
            ])
            ->defaultSort(fn ($query) => $query
                ->orderBy('created_at', 'asc')
            )
            ->filters([
                // Filter 1: Rentang Tanggal
                Tables\Filters\Filter::make('periode')
                    ->form([
                        // PERBAIKAN 2: Menggunakan Forms\Components\DatePicker (bukan Tables\...)
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $q) => $q->whereHas('jurnal', fn($j) => $j->whereDate('tanggal', '>=', $data['dari_tanggal']))
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $q) => $q->whereHas('jurnal', fn($j) => $j->whereDate('tanggal', '<=', $data['sampai_tanggal']))
                            );
                    }),

                // Filter 2: Pilih Akun
                Tables\Filters\SelectFilter::make('akun_id')
                    ->label('Filter Akun')
                    ->relationship('akun', 'nama_akun')
                    ->preload()
                    ->searchable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Laporan_Jurnal_Umum_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('jurnal.tanggal')->heading('Tanggal'),
                                Column::make('jurnal.transaksi_id')->heading('No Ref'),
                                Column::make('akun.kode_akun')->heading('Kode Akun'),
                                Column::make('akun.nama_akun')->heading('Nama Akun'),
                                Column::make('jurnal.keterangan')->heading('Keterangan'),
                                Column::make('debit')->heading('Debit'),
                                Column::make('kredit')->heading('Kredit'),
                            ]),
                    ]),
            ])
            ->actions([]) 
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanBukuBesars::route('/'),
        ];
    }
}