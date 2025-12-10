<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
// PERBAIKAN DI SINI (Gunakan Namespace yang benar):
use Filament\Forms\Components\FileUpload; 
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Produk')
                    ->schema([
                        Select::make('kategori_id')
                            ->relationship('kategori', 'nama_kategori')
                            ->required()
                            ->label('Kategori Menu'),

                        TextInput::make('nama_produk')
                            ->required()
                            ->maxLength(100)
                            ->label('Nama Menu'),

                        FileUpload::make('gambar')
                            ->image() 
                            ->directory('products') 
                            ->columnSpanFull(),

                        TextInput::make('harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Harga Satuan'),

                        TextInput::make('stok')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->label('Stok Awal'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar')->square(),
                
                TextColumn::make('nama_produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Menu'),

                TextColumn::make('kategori.nama_kategori')
                    ->sortable()
                    ->badge()
                    ->label('Kategori'),

                TextColumn::make('harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => $state <= 5 ? 'danger' : 'success')
                    ->label('Sisa Stok'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama_kategori'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}