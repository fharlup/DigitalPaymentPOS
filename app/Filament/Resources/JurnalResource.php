<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JurnalResource\Pages;
use App\Filament\Resources\JurnalResource\RelationManagers;
use App\Models\Jurnal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JurnalResource extends Resource
{
    protected static ?string $model = Jurnal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Header Jurnal')
                ->schema([
                    DatePicker::make('tanggal')
                        ->required()
                        ->default(now()),
                    TextInput::make('keterangan')
                        ->required()
                        ->maxLength(255),
                ]),

            Section::make('Detail Jurnal')
                ->schema([
                    Repeater::make('detailJurnals')
                        ->relationship()
                        ->schema([
                            Select::make('akun_id')
                                ->relationship('akun', 'nama_akun')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode_akun} - {$record->nama_akun}")
                                ->required()
                                ->columnSpan(2), // Lebar kolom
                            
                            TextInput::make('debit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                                
                            TextInput::make('kredit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                        ])
                        ->columns(4) 
                        ->defaultItems(2) 
                        ->addActionLabel('Tambah Baris Akun'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               Tables\Columns\TextColumn::make('tanggal')->date(),
            Tables\Columns\TextColumn::make('keterangan')->limit(50),
            // Menghitung total Debit buat ngecek balance (opsional tampilan)
            Tables\Columns\TextColumn::make('detailJurnals_sum_debit')
                ->sum('detailJurnals', 'debit')
                ->money('IDR')
                ->label('Total Debit'), 
                
            ])
->defaultSort('tanggal', 'desc') 
            ->filters([
                //
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
            'index' => Pages\ListJurnals::route('/'),
            'create' => Pages\CreateJurnal::route('/create'),
            'edit' => Pages\EditJurnal::route('/{record}/edit'),
        ];
    }
}
