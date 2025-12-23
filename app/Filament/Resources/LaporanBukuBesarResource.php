<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBukuBesarResource\Pages;
use App\Models\DetailJurnal; // <--- PENTING: Arahkan ke Model DetailJurnal
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;

class LaporanBukuBesarResource extends Resource
{
    protected static ?string $model = DetailJurnal::class; // Pakai Model DetailJurnal

    // Setting Nama Menu
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