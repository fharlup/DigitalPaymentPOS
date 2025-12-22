<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Transaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // Icon Dolar
    protected static ?string $navigationLabel = 'Daftar Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->required()
                    ->maxLength(255),

                // INI YANG PENTING: NOMOR MEJA
                Forms\Components\TextInput::make('no_meja')
                    ->label('Nomor Meja')
                    ->disabled() // Admin cuma baca, ga usah edit
                    ->dehydrated(false), // Biar ga error saat save

                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Bayar')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Belum Bayar',
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                    ])
                    ->required(),
                
                Forms\Components\Select::make('metode_pembayaran')
                    ->options([
                        'tunai' => 'Tunai (Cash)',
                        'qris' => 'QRIS (Online)',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. NAMA PELANGGAN
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->searchable()
                    ->weight('bold'),

                // 2. NOMOR MEJA (PENTING!)
                Tables\Columns\TextColumn::make('no_meja')
                    ->label('Meja')
                    ->badge() 
                    ->color('info') // Warna Biru
                    ->sortable(),

                // 3. TOTAL HARGA
                Tables\Columns\TextColumn::make('total_harga')
                    ->money('IDR')
                    ->sortable(),

                // 4. STATUS PEMBAYARAN
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',   // Hijau
                        'pending' => 'warning', // Kuning
                        'failed' => 'danger',   // Merah
                        default => 'gray',
                    }),

                // 5. METODE BAYAR
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pesan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                // Filter Lunas/Belum
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Belum Bayar',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}