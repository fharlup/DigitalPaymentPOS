<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Actions\Action;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class BukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $title = 'Buku Besar';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static string $view = 'filament.pages.buku-besar';

    public ?array $data = [];
    public $reportData = [];
    public $expandedRows = []; // Track akun mana yang di-expand

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date'   => now()->format('Y-m-d'),
        ]);

        $this->filter();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required(),
                    ]),
            ])->statePath('data');
    }

    public function toggleRow($akunId): void
    {
        if (in_array($akunId, $this->expandedRows)) {
            $this->expandedRows = array_values(array_filter(
                $this->expandedRows,
                fn($id) => $id !== $akunId
            ));
        } else {
            $this->expandedRows[] = $akunId;
        }
    }

    public function filter(): void
    {
        $data      = $this->form->getState();
        $startDate = $data['start_date'];
        $endDate   = $data['end_date'];

        $akuns = Akun::orderBy('kode_akun', 'asc')->get();

        $this->reportData   = [];
        $this->expandedRows = []; // Reset expand saat filter

        foreach ($akuns as $akun) {
            $kodeDepan = substr($akun->kode_akun, 0, 1);
            $isDebit   = in_array($kodeDepan, ['1', '5', '6', '8', '9']);

            // Saldo Awal
            $history    = DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q->where('tanggal', '<', $startDate))
                ->get();
            $saldoAwal  = $isDebit
                ? ($history->sum('debit') - $history->sum('kredit'))
                : ($history->sum('kredit') - $history->sum('debit'));

            // Mutasi periode
            $mutasiRows = DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
                ->with('jurnal')
                ->orderBy('id', 'asc')
                ->get();

            $debitMutasi  = $mutasiRows->sum('debit');
            $kreditMutasi = $mutasiRows->sum('kredit');
            $saldoAkhir   = $isDebit
                ? ($saldoAwal + $debitMutasi - $kreditMutasi)
                : ($saldoAwal + $kreditMutasi - $debitMutasi);

            // Build detail transaksi dengan running saldo
            $runningSaldo = $saldoAwal;
            $transaksi    = [];

            foreach ($mutasiRows as $detail) {
                $runningSaldo = $isDebit
                    ? ($runningSaldo + $detail->debit - $detail->kredit)
                    : ($runningSaldo + $detail->kredit - $detail->debit);

                $transaksi[] = [
                    'tanggal'     => $detail->jurnal->tanggal,
                    'keterangan'  => trim(preg_replace('/#\d+/', '', $detail->jurnal->keterangan)),
                    'ref'         => $detail->jurnal->id,
                    'debit'       => $detail->debit,
                    'kredit'      => $detail->kredit,
                    'saldo'       => $runningSaldo,
                ];
            }

            $this->reportData[] = [
                'id'         => $akun->id,
                'kode'       => $akun->kode_akun,
                'nama'       => $akun->nama_akun,
                'tipe'       => $akun->tipe ?? 'Umum',
                'saldo_awal' => $saldoAwal,
                'debit'      => $debitMutasi,
                'kredit'     => $kreditMutasi,
                'saldo_akhir'=> $saldoAkhir,
                'transaksi'  => $transaksi,
            ];
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }
}