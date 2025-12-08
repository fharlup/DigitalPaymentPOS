<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunResource\Pages;
use App\Filament\Resources\AkunResource\RelationManagers;
use App\Models\Akun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AkunResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_akun')
                ->required()
                ->numeric()
                ->label('Kode Akun (Cth: 111)'),
                
            Forms\Components\TextInput::make('nama_akun')
                ->required()
                ->label('Nama Akun (Cth: Kas)'),
                
            Forms\Components\Select::make('tipe')
                ->options([
                    'debit' => 'Debit (Aset/Beban)',
                    'kredit' => 'Kredit (Kewajiban/Modal/Pendapatan)',
                ])
                ->required()
                ->label('Saldo Normal'),
        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')->sortable(),
            Tables\Columns\TextColumn::make('nama_akun')->searchable(),
            Tables\Columns\TextColumn::make('tipe')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'debit' => 'info',
                    'kredit' => 'success',
                }),
            ])
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
            'index' => Pages\ListAkuns::route('/'),
            'create' => Pages\CreateAkun::route('/create'),
            'edit' => Pages\EditAkun::route('/{record}/edit'),
        ];
    }
}
