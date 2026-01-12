<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBukuBesarResource\Pages;
use App\Models\DetailJurnal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms; 
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
    protected static ?string $modelLabel = 'Jurnal Umum';      
    protected static ?string $pluralModelLabel = 'Jurnal Umum'; 
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
                // TANGGAL
                Tables\Columns\TextColumn::make('jurnal.tanggal')
                    ->label('Tanggal')
                    ->date('d M Y') 
                    ->sortable(),
                
                // REF (Bersih: Kode Akun saja)
                Tables\Columns\TextColumn::make('akun.kode_akun')
                    ->label('Ref')
                    ->fontFamily('mono')
                    ->sortable()
                    ->searchable(), 

                // NAMA AKUN
                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Akun')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                // --- KETERANGAN (YANG DIHAPUS # NYA) ---
                Tables\Columns\TextColumn::make('jurnal.keterangan')
                    ->label('Keterangan')
                    // Rumus: Cari tanda # diikuti angka, lalu hapus (ganti dengan string kosong)
                    ->formatStateUsing(fn (string $state) => trim(preg_replace('/#\d+/', '', $state)))
                    ->limit(40)
                    ->tooltip(fn (DetailJurnal $record) => trim(preg_replace('/#\d+/', '', $record->jurnal->keterangan))),
                
                // DEBIT
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->numeric(decimalPlaces: 0)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray') 
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),
                
                // KREDIT
                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->numeric(decimalPlaces: 0)
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray') 
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),
            ])
            
            // SORTING
            ->defaultSort(fn ($query) => $query
                ->select('detail_jurnals.*') 
                ->join('jurnals', 'detail_jurnals.jurnal_id', '=', 'jurnals.id')
                ->orderBy('jurnals.tanggal', 'asc')
                ->orderBy('jurnals.created_at', 'asc')
                ->orderBy('detail_jurnals.debit', 'desc') 
            )
            
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn ($q) => $q->whereHas('jurnal', fn($j) => $j->whereDate('tanggal', '>=', $data['dari_tanggal'])))
                            ->when($data['sampai_tanggal'], fn ($q) => $q->whereHas('jurnal', fn($j) => $j->whereDate('tanggal', '<=', $data['sampai_tanggal'])));
                    }),
                
                Tables\Filters\SelectFilter::make('akun_id')
                    ->label('Filter Akun')
                    ->relationship('akun', 'nama_akun')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Jurnal_Umum_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('jurnal.tanggal')->heading('Tanggal'),
                                Column::make('akun.kode_akun')->heading('Ref'),
                                Column::make('akun.nama_akun')->heading('Akun'),
                                Column::make('jurnal.keterangan')->heading('Keterangan')
                                    ->formatStateUsing(fn ($state) => trim(preg_replace('/#\d+/', '', $state))), // Excel juga dibersihkan
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