<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class BukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $title = 'Laporan Buku Besar';
    protected static ?string $navigationGroup = 'Laporan Keuangan';
    protected static string $view = 'filament.pages.buku-besar';

    // Properti untuk Filter
    public ?array $data = [];
    
    // Properti untuk Data Laporan
    public $ledgerData = [];
    public $saldoAwal = 0;
    public $totalDebit = 0;
    public $totalKredit = 0;
    public $selectedAkun = null;

    public function mount(): void
    {
        // Default: Awal bulan ini sampai hari ini
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'akun_id' => Akun::first()?->id, // Default akun pertama
        ]);

        $this->filter(); // Load data awal
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Select::make('akun_id')
                            ->label('Pilih Akun')
                            ->options(Akun::all()->pluck('nama_akun', 'id'))
                            ->searchable()
                            ->required()
                            ->live(), // Auto reload jika ganti akun
                            
                        DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->required(),
                            
                        DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ])
                    ->columns(3)
            ])->statePath('data');
    }

    // Function yang dipanggil tombol "Tampilkan"
    public function filter()
    {
        $data = $this->form->getState();
        $akunId = $data['akun_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        $this->selectedAkun = Akun::find($akunId);
        
        if (!$this->selectedAkun) return;

        // 1. HITUNG SALDO AWAL (Transaksi SEBELUM start_date)
        $historyBefore = DetailJurnal::where('akun_id', $akunId)
            ->whereHas('jurnal', fn($q) => $q->where('tanggal', '<', $startDate))
            ->get();

        // Rumus Saldo: Jika akun Debit (Harta/Beban) = Debit - Kredit
        // Jika akun Kredit (Utang/Modal/Pendapatan) = Kredit - Debit
        $isDebitAccount = $this->selectedAkun->tipe == 'debit'; 

        $debitAwal = $historyBefore->sum('debit');
        $kreditAwal = $historyBefore->sum('kredit');

        $this->saldoAwal = $isDebitAccount 
            ? ($debitAwal - $kreditAwal) 
            : ($kreditAwal - $debitAwal);

        // 2. AMBIL TRANSAKSI PERIODE INI
        $this->ledgerData = DetailJurnal::with('jurnal')
            ->where('akun_id', $akunId)
            ->whereHas('jurnal', fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
            ->get()
            ->sortBy(fn($detail) => $detail->jurnal->tanggal . $detail->created_at); // Urutkan tanggal lalu jam

        // Hitung total mutasi periode ini
        $this->totalDebit = $this->ledgerData->sum('debit');
        $this->totalKredit = $this->ledgerData->sum('kredit');
    }
    
    // Tambahkan Tombol Filter di Header Form
    protected function getFormActions(): array
    {
        return [
            Action::make('filter')
                ->label('Tampilkan Data')
                ->submit('filter'),
        ];
    }
}