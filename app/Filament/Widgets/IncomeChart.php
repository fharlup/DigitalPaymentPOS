<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Total Pemasukan';
    protected static ?int $sort = 2; // Urutan ke-2 setelah kotak statistik

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Via POS',
                    'data' => [14, 17, 6, 16, 12, 17, 21],
                    'backgroundColor' => '#3b82f6', // Biru
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Via Kasir',
                    'data' => [12, 11, 23, 7, 11, 13, 11],
                    'backgroundColor' => '#22c55e', // Hijau
                    'borderColor' => '#22c55e',
                ],
            ],
            'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}