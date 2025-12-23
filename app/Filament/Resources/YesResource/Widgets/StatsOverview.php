<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sales', 'Rp 100.000')
                ->description('+8% from yesterday')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger') // Merah/Pink (sesuai gambar)
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Grafik kecil di belakang
                ->icon('heroicon-o-chart-bar'), // Icon grafik

            Stat::make('Total Pesanan', '300')
                ->description('+5% lebih banyak')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning') // Kuning/Orange
                ->icon('heroicon-o-document-text'),

            Stat::make('Produk Terjual', '5')
                ->description('+1.2% lebih banyak')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success') // Hijau
                ->icon('heroicon-o-tag'),

            Stat::make('Konsumen Hari Ini', '8')
                ->description('0.5% lebih banyak')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info') // Biru/Ungu
                ->icon('heroicon-o-users'),
        ];
    }
}