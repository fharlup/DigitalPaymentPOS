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

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Daftar Pesanan';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static ?int $navigationSort = 0;

    // Form tetap dibutuhkan karena 'ViewAction' akan menggunakan schema ini untuk menampilkan detail
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pelanggan')
                    ->label('Nama Pelanggan'), // Tidak perlu required/maxLength karena view only
                
                Forms\Components\TextInput::make('no_meja')
                    ->label('Nomor Meja'),

                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Bayar')
                    ->numeric()
                    ->prefix('Rp'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Belum Bayar',
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                    ]),
                
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
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('no_meja')
                    ->label('Meja')
                    ->badge() 
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pesan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Lunas',
                        'pending' => 'Belum Bayar',
                    ]),
            ])
            ->actions([
                // GANTI EditAction MENJADI ViewAction
                // Tables\Actions\EditAction::make(), <--- HAPUS INI
                Tables\Actions\ViewAction::make(), // <--- PAKAI INI (Tombol Mata)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            // HAPUS route 'create' dan 'edit' agar tombol "New Transaksi" hilang otomatis
            'index' => Pages\ListTransaksis::route('/'),
            // 'create' => Pages\CreateTransaksi::route('/create'), // <--- HAPUS
            // 'edit' => Pages\EditTransaksi::route('/{record}/edit'), // <--- HAPUS
        ];
    }
    
    // TAMBAHAN: Memastikan tombol "New" benar-benar hilang secara logic
    public static function canCreate(): bool
    {
        return false;
    }
}