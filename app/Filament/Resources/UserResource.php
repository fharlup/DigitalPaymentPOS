<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengguna')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->label('Nama Lengkap'),
                    
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->label('Email Address'),

                    // Password hanya required saat create (buat baru)
                    TextInput::make('password')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->label('Password'),

                    // Pilihan Role sesuai TA: Kasir atau Admin
                    Select::make('role')
                        ->options([
                            'admin' => 'Bagian Penjualan (Admin)',
                            'kasir' => 'Kasir',
                        ])
                        ->required()
                        ->label('Role / Jabatan'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->label('Nama'),
            
            TextColumn::make('email')
                ->searchable()
                ->label('Email'),
            
            TextColumn::make('role')
                ->badge() 
                ->color(fn (string $state): string => match ($state) {
                    'admin' => 'success', 
                    'kasir' => 'warning', 
                })
                ->sortable()
                ->label('Jabatan'),
                
            TextColumn::make('created_at')
                ->dateTime()
                ->label('Dibuat Pada'),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
