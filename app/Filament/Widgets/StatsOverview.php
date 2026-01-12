<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    // Auto refresh data setiap 5 detik
    protected static ?string $pollingInterval = '5s';
    
    // Urutan paling atas (di atas grafik)
    protected static ?int $sort = 1; 

    protected function getStats(): array
    {
        $hariIni = Carbon::today();

        // 1. HITUNG OMZET HARI INI (Hanya yang status Lunas/Done)
        $omzet = Transaksi::whereDate('created_at', $hariIni)
            ->whereIn('status', ['paid', 'done'])
            ->sum('total_harga');

        // 2. HITUNG JUMLAH PESANAN HARI INI
        $jumlahPesanan = Transaksi::whereDate('created_at', $hariIni)->count();

        // 3. HITUNG YANG BELUM BAYAR (Pending)
        $pending = Transaksi::where('status', 'pending')->count();

        return [
            // KARTU 1: OMZET
            Stat::make('Omzet Hari Ini', 'Rp ' . number_format($omzet, 0, ',', '.'))
                ->description('Pemasukan bersih hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'), // Hijau

            // KARTU 2: JUMLAH PESANAN
            Stat::make('Pesanan Masuk', $jumlahPesanan)
                ->description('Total struk hari ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'), // Biru/Ungu

            // KARTU 3: STATUS PENDING
            Stat::make('Belum Bayar', $pending)
                ->description('Meja yang masih makan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'danger' : 'success'), // Merah kalau ada yang pending
        ];
    }
}