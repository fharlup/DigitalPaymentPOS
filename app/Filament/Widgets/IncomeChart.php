<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaksi;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Total Pemasukan (30 Hari Terakhir)';
    protected static ?int $sort = 2;
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
            ->sum('total_harga'); // Jumlahkan total harga

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan (Rp)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#3b82f6', // Biru
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}