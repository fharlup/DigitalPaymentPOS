<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Transaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Daftar Pesanan';
    protected static ?string $pluralModelLabel = 'Daftar Pesanan';
    protected static ?string $navigationGroup = 'Laporan Keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pelanggan')
                    ->label('Nama Pelanggan'),
                
                Forms\Components\TextInput::make('no_meja')
                    ->label('Nomor Meja'),

                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Bayar')
                    ->numeric()
                    ->prefix('Rp'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Belum Bayar',
                        'paid'    => 'Lunas',
                        'failed'  => 'Gagal',
                        'done'    => 'Selesai',
                    ]),
                
                Forms\Components\Select::make('metode_pembayaran')
                    ->options([
                        'tunai' => 'Tunai (Cash)',
                        'qris'  => 'QRIS (Online)',
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
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        'done'    => 'info',
                        default   => 'gray',
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
                    ->label('Status')
                    ->options([
                        'paid'    => 'Lunas',
                        'pending' => 'Belum Bayar',
                        'failed'  => 'Gagal',
                        'done'    => 'Selesai',
                    ]),

                Tables\Filters\Filter::make('periode')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTransaksis::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}