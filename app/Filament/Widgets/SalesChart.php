<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaksi;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Frekuensi Penjualan (30 Hari Terakhir)';
    protected static ?int $sort = 3;
    protected static string $color = 'success';
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        // PERBAIKAN: Masukkan filter langsung di dalam Trend::query()
        $data = Trend::query(
                Transaksi::whereIn('status', ['paid', 'done'])
            )
            ->between(
                start: now()->subDays(29),
                end: now(),
            )
            ->perDay()
            ->count(); // Hitung jumlah

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981', // Hijau
                    'fill' => true,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}