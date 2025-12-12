<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    // Agar widget ini muncul paling atas
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Hitung Omset Hari Ini (Status Paid)
        $omset = Transaksi::whereDate('created_at', Carbon::today())
            ->where('status', 'paid')
            ->sum('total_harga');

        // 2. Total Pesanan Hari Ini
        $pesanan = Transaksi::whereDate('created_at', Carbon::today())->count();

        // 3. Produk Terjual Hari Ini
        $produkTerjual = DetailTransaksi::whereHas('transaksi', function ($query) {
            $query->whereDate('created_at', Carbon::today())
                  ->where('status', 'paid');
        })->sum('jumlah');

        // 4. Jumlah Pelanggan Unik Hari Ini
        $pelanggan = Transaksi::whereDate('created_at', Carbon::today())
            ->distinct('nama_pelanggan')
            ->count('nama_pelanggan');

        return [
            Stat::make('Omset Hari Ini', 'Rp ' . number_format($omset, 0, ',', '.'))
                ->description('Pemasukan bersih')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger') // Merah/Pink
                ->chart([7, 3, 10, 5, 15, 8, 20]), // Grafik mini hiasan

            Stat::make('Total Pesanan', $pesanan . ' Transaksi')
                ->description('Antrian masuk')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'), // Orange

            Stat::make('Produk Terjual', $produkTerjual . ' Porsi')
                ->description('Item keluar dapur')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'), // Hijau

            Stat::make('Pelanggan', $pelanggan . ' Orang')
                ->description('Datang hari ini')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'), // Ungu/Biru
        ];
    }
}