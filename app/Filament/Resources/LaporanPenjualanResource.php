<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Models\Transaksi; // <--- PENTING: Arahkan ke Model Transaksi
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;

class LaporanPenjualanResource extends Resource
{
    protected static ?string $model = Transaksi::class; // Pakai Model Transaksi

    // Setting Nama Menu
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $slug = 'laporan-penjualan';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 1;

    // Filter: Hanya ambil yang LUNAS
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'paid')->orderBy('created_at', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_meja')
                    ->label('Meja')
                    ->badge()->color('warning'),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tunai' => 'success',
                        'qris' => 'info',
                    }),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Omzet')
                    ->money('IDR')
                    ->summarize(Sum::make()->label('Total')),
            ])
            ->filters([
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
            ->actions([]) // Read Only
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPenjualans::route('/'),
        ];
    }
}