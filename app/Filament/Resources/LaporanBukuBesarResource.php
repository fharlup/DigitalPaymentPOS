<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBukuBesarResource\Pages;
use App\Models\DetailJurnal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;

use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanBukuBesarResource extends Resource
{
    protected static ?string $model = DetailJurnal::class;

    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $modelLabel = 'Jurnal Umum';
    protected static ?string $pluralModelLabel = 'Jurnal Umum';
    protected static ?string $slug = 'jurnal-umum';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('jurnal.transaksi_id')
                    ->label('Nomor Transaksi')
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('jurnal.tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder(''),

                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Keterangan')
                    ->weight(fn (DetailJurnal $record) => $record->debit > 0 ? 'bold' : 'normal')
                    ->searchable(),

                Tables\Columns\TextColumn::make('akun.kode_akun')
                    ->label('Ref')
                    ->fontFamily('mono')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->formatStateUsing(fn ($state) => $state > 0
                        ? 'Rp ' . number_format($state, 0, ',', '.')
                        : '')
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->formatStateUsing(fn ($state) => $state > 0
                        ? 'Rp ' . number_format($state, 0, ',', '.')
                        : '')
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),
            ])

            ->defaultSort(fn ($query) => $query
                ->select('detail_jurnals.*')
                ->join('jurnals', 'detail_jurnals.jurnal_id', '=', 'jurnals.id')
                ->orderBy('jurnals.tanggal', 'asc')
                ->orderBy('jurnals.created_at', 'asc')
                ->orderBy('detail_jurnals.debit', 'desc')
            )

            ->filters([
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
                                fn ($q) => $q->whereHas('jurnal', fn ($j) =>
                                    $j->whereDate('tanggal', '>=', $data['dari_tanggal']))
                            )
                            ->when(
                                filled($data['sampai_tanggal'] ?? null),
                                fn ($q) => $q->whereHas('jurnal', fn ($j) =>
                                    $j->whereDate('tanggal', '<=', $data['sampai_tanggal']))
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

                Tables\Filters\SelectFilter::make('akun_id')
                    ->label('Filter Akun')
                    ->relationship('akun', 'nama_akun')
                    ->searchable()
                    ->preload(),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)

            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Jurnal_Umum_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('jurnal.tanggal')->heading('Tanggal'),
                                Column::make('akun.nama_akun')->heading('Keterangan'),
                                Column::make('akun.kode_akun')->heading('Ref'),
                                Column::make('debit')->heading('Debit'),
                                Column::make('kredit')->heading('Kredit'),
                            ]),
                    ]),
            ])
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