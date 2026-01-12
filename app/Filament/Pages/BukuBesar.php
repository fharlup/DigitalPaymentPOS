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
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $title = ' Buku Besar';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static string $view = 'filament.pages.buku-besar';

    public ?array $data = [];
    public $reportData = []; // Menampung data semua akun

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        $this->filter();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // Layout 3 kolom biar tombol Filter di kanan
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

    public function filter()
    {
        $data = $this->form->getState();
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        // Ambil semua akun
        $akuns = Akun::orderBy('kode_akun', 'asc')->get();
        
        $this->reportData = [];

        foreach ($akuns as $akun) {
            // 1. Hitung Saldo Awal (Transaksi Sebelum Start Date)
            $history = DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q->where('tanggal', '<', $startDate))
                ->get();
            
            $debitLalu = $history->sum('debit');
            $kreditLalu = $history->sum('kredit');

            // Cek Tipe Akun untuk Rumus
            // Asumsi sederhana: Kode depan 1,5,6,8,9 = DEBIT. Sisanya KREDIT.
            $kodeDepan = substr($akun->kode_akun, 0, 1);
            $isDebit = in_array($kodeDepan, ['1', '5', '6', '8', '9']);

            $saldoAwal = $isDebit ? ($debitLalu - $kreditLalu) : ($kreditLalu - $debitLalu);

            // 2. Hitung Mutasi Periode Ini
            $mutasi = DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
                ->get();

            $debitMutasi = $mutasi->sum('debit');
            $kreditMutasi = $mutasi->sum('kredit');

            // 3. Hitung Saldo Akhir
            $saldoAkhir = $isDebit 
                ? ($saldoAwal + $debitMutasi - $kreditMutasi)
                : ($saldoAwal + $kreditMutasi - $debitMutasi);

            // Masukkan ke array data hanya jika ada nilainya (Opsional, hapus if ini jika mau tampil semua)
            // if ($saldoAwal != 0 || $debitMutasi != 0 || $kreditMutasi != 0) {
                $this->reportData[] = [
                    'id' => $akun->id,
                    'kode' => $akun->kode_akun,
                    'nama' => $akun->nama_akun,
                    'tipe' => $akun->tipe ?? 'Umum', // Pastikan ada kolom tipe di tabel akun
                    'saldo_awal' => $saldoAwal,
                    'debit' => $debitMutasi,
                    'kredit' => $kreditMutasi,
                    'saldo_akhir' => $saldoAkhir,
                ];
            // }
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('filter')
                ->label('Filter')
                ->color('primary') // Warna hitam/gelap
                ->submit('filter'),
        ];
    }
}