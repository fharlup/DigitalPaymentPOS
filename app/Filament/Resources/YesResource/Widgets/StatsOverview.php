<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    // Refresh otomatis setiap 5 detik
    protected static ?string $pollingInterval = '5s';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. SIAPKAN TANGGAL
        $hariIni = Carbon::today();
        $kemarin = Carbon::yesterday();

        // ==========================================
        // DATA 1: TOTAL SALES (OMZET)
        // ==========================================
        
        // Hitung Omzet Hari Ini
        $omzetNow = Transaksi::whereDate('created_at', $hariIni)
            ->whereIn('status', ['paid', 'done'])
            ->sum('total_harga');

        // Hitung Omzet Kemarin (Untuk perbandingan)
        $omzetYesterday = Transaksi::whereDate('created_at', $kemarin)
            ->whereIn('status', ['paid', 'done'])
            ->sum('total_harga');

        // Hitung Persentase Kenaikan/Penurunan
        $diffOmzet = $omzetNow - $omzetYesterday;
        $persenOmzet = $omzetYesterday > 0 ? round(($diffOmzet / $omzetYesterday) * 100) : 100;
        
        // Tentukan Icon & Warna (Naik = Hijau, Turun = Merah)
        $iconOmzet = $diffOmzet >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorOmzet = $diffOmzet >= 0 ? 'success' : 'danger';
        $descOmzet = $diffOmzet >= 0 ? "+{$persenOmzet}% dari kemarin" : "{$persenOmzet}% dari kemarin";

        // ==========================================
        // DATA 2: TOTAL PESANAN (TRANSAKSI)
        // ==========================================
        
        $orderNow = Transaksi::whereDate('created_at', $hariIni)->count();
        $orderYesterday = Transaksi::whereDate('created_at', $kemarin)->count();

        $diffOrder = $orderNow - $orderYesterday;
        $persenOrder = $orderYesterday > 0 ? round(($diffOrder / $orderYesterday) * 100) : 100;
        
        $iconOrder = $diffOrder >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorOrder = $diffOrder >= 0 ? 'success' : 'danger';
        $descOrder = $diffOrder >= 0 ? "+{$persenOrder}% lebih banyak" : "{$persenOrder}% menurun";

        // ==========================================
        // CHART DATA (Untuk Gelombang di Background)
        // ==========================================
        $chartData = $this->getChartData();

        return [
            // KARTU 1: TOTAL SALES
            Stat::make('Total Sales', 'Rp ' . number_format($omzetNow, 0, ',', '.'))
                ->description($descOmzet)           // Teks: +10% dari kemarin
                ->descriptionIcon($iconOmzet)       // Ikon: Panah Naik/Turun
                ->color($colorOmzet)                // Warna: Hijau/Merah
                ->chart($chartData),                // Grafik Gelombang di background

            // KARTU 2: TOTAL PESANAN
            Stat::make('Total Pesanan', $orderNow)
                ->description($descOrder)
                ->descriptionIcon($iconOrder)
                ->color($colorOrder)
                ->chart([$orderYesterday, $orderNow]), // Grafik simpel 2 titik

            // KARTU 3: KONSUMEN / MEJA AKTIF (PENDING)
            Stat::make('Meja Aktif (Pending)', Transaksi::where('status', 'pending')->count())
                ->description('Sedang makan sekarang')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }

    // Fungsi Grafik 7 Hari (Biar ada gelombangnya)
    private function getChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Transaksi::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'done'])
                ->sum('total_harga');
            $data[] = $total;
        }
        return $data;
    }
}