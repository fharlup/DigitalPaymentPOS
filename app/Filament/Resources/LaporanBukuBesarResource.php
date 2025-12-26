<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBukuBesarResource\Pages;
use App\Models\DetailJurnal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;

// Import untuk Excel
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanBukuBesarResource extends Resource
{
    protected static ?string $model = DetailJurnal::class;

    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $slug = 'laporan-buku-besar';
    protected static ?string $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jurnal.tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('jurnal.keterangan')
                    ->label('Keterangan'),
                
                Tables\Columns\TextColumn::make('debit')
                    ->money('IDR')
                    ->summarize(Sum::make()->label('Total Debit')),
                
                Tables\Columns\TextColumn::make('kredit')
                    ->money('IDR')
                    ->summarize(Sum::make()->label('Total Kredit')),
            ])
            ->filters([
                // Filter Wajib: Pilih Akun
                Tables\Filters\SelectFilter::make('akun_id')
                    ->label('Filter Akun')
                    ->relationship('akun', 'nama_akun')
                    ->preload()
                    ->searchable(),
            ])
            ->headerActions([
                // --- TOMBOL EXPORT EXCEL ---
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success') // Warna Hijau
                    ->exports([
                        ExcelExport::make()
                            ->fromTable() // Mengikuti data yang difilter di tabel
                            ->withFilename('Buku_Besar_' . date('Y-m-d'))
                            ->withColumns([
                                // Kolom Relasi (Dot Notation)
                                Column::make('jurnal.tanggal')->heading('Tanggal'),
                                Column::make('akun.nama_akun')->heading('Nama Akun'),
                                Column::make('jurnal.keterangan')->heading('Keterangan'),
                                
                                // Kolom Angka
                                Column::make('debit')->heading('Debit'),
                                Column::make('kredit')->heading('Kredit'),
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
            'index' => Pages\ListLaporanBukuBesars::route('/'),
        ];
    }
}