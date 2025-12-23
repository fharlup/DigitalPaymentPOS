<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan';
    protected static ?int $sort = 3; 

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Bulan Kemarin',
                    'data' => [65, 59, 80, 81, 56, 55, 40],
                    'borderColor' => '#3b82f6', // Biru
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', // Biru transparan
                    'fill' => true,
                    'tension' => 0.4, // Membuat garis melengkung halus
                ],
                [
                    'label' => 'Bulan Ini',
                    'data' => [28, 48, 40, 19, 86, 27, 90],
                    'borderColor' => '#22c55e', // Hijau
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)', // Hijau transparan
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}