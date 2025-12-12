<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaksi;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pemasukan (7 Hari Terakhir)';
    protected static ?int $sort = 2; // Muncul di bawah kotak statistik

    protected function getData(): array
    {
        // Ambil data 7 hari terakhir
        $data = Trend::model(Transaksi::class)
            ->between(
                start: now()->subDays(6),
                end: now(),
            )
            ->perDay()
            ->sum('total_harga');

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan (Rp)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    // Warna-warni tiap batang biar mirip referensi kamu
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(255, 205, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(201, 203, 207, 0.5)'
                    ],
                    'borderColor' => 'transparent',
                    'barThickness' => 30,
                    'borderRadius' => 5,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}