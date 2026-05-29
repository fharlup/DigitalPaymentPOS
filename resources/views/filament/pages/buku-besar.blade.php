<x-filament-panels::page>

    {{-- FORM FILTER --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-end gap-4">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="mb-4">
                <x-filament::button wire:click="filter" color="gray">
                    <x-heroicon-m-funnel class="w-4 h-4 inline mr-1"/> Filter
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- KONTEN LAPORAN --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- HEADER --}}
        <div class="p-6 bg-[#3E2F29] text-white">
            <h2 class="text-xl font-bold uppercase tracking-wide">KEDAI KOPI AMPUH</h2>
            <p class="text-gray-300 text-sm font-medium mt-1">Buku Besar</p>
            <p class="text-gray-400 text-xs mt-1">
                Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }}
                - {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
            </p>
        </div>

        {{-- INFO BAR --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <span class="text-sm text-gray-600">
                Total akun: <strong>{{ count($reportData) }}</strong>
            </span>
            <span class="text-xs text-gray-400">Klik baris untuk lihat detail transaksi</span>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-4 py-4 w-8"></th>
                        <th class="px-4 py-4">KODE</th>
                        <th class="px-4 py-4">NAMA AKUN</th>
                        <th class="px-4 py-4 text-center">TIPE</th>
                        <th class="px-4 py-4 text-right">SALDO AWAL</th>
                        <th class="px-4 py-4 text-right">DEBIT</th>
                        <th class="px-4 py-4 text-right">KREDIT</th>
                        <th class="px-4 py-4 text-right">SALDO AKHIR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        @php
                            $isExpanded = in_array($row['id'], $expandedRows);
                            $badgeColor = match(strtolower($row['tipe'])) {
                                'aset', 'harta'      => 'bg-green-100 text-green-700',
                                'beban', 'biaya'     => 'bg-red-100 text-red-700',
                                'pendapatan'         => 'bg-blue-100 text-blue-700',
                                'modal'              => 'bg-purple-100 text-purple-700',
                                default              => 'bg-gray-100 text-gray-600',
                            };
                        @endphp

                        {{-- ROW SUMMARY --}}
                        <tr
                            wire:click="toggleRow({{ $row['id'] }})"
                            class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors {{ $isExpanded ? 'bg-gray-50' : '' }}"
                        >
                            {{-- TOGGLE ICON --}}
                            <td class="px-4 py-3 text-gray-400">
                                @if(count($row['transaksi']) > 0)
                                    @if($isExpanded)
                                        <x-heroicon-m-chevron-down class="w-4 h-4 text-gray-500"/>
                                    @else
                                        <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400"/>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600">{{ $row['kode'] }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row['nama'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase {{ $badgeColor }}">
                                    {{ $row['tipe'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                Rp {{ number_format($row['debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                Rp {{ number_format($row['kredit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">
                                Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- DETAIL TRANSAKSI (expand) --}}
                        @if($isExpanded)
                            <tr>
                                <td colspan="8" class="px-0 py-0 bg-blue-50/40">
                                    <div class="px-8 py-3">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-gray-400 uppercase tracking-wider border-b border-gray-200">
                                                    <th class="py-2 text-left font-semibold">Tanggal</th>
                                                    <th class="py-2 text-left font-semibold">Keterangan</th>
                                                    <th class="py-2 text-left font-semibold">Ref</th>
                                                    <th class="py-2 text-right font-semibold">Debit</th>
                                                    <th class="py-2 text-right font-semibold">Kredit</th>
                                                    <th class="py-2 text-right font-semibold">Saldo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Baris saldo awal --}}
                                                <tr class="text-gray-400 italic">
                                                    <td class="py-1.5">—</td>
                                                    <td class="py-1.5" colspan="4">Saldo Awal</td>
                                                    <td class="py-1.5 text-right font-medium text-gray-600">
                                                        Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}
                                                    </td>
                                                </tr>

                                                @forelse($row['transaksi'] as $trx)
                                                    <tr class="border-t border-gray-100 hover:bg-blue-50 transition-colors">
                                                        <td class="py-1.5 text-gray-500">
                                                            {{ \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') }}
                                                        </td>
                                                        <td class="py-1.5 text-gray-700">{{ $trx['keterangan'] }}</td>
                                                        <td class="py-1.5 font-mono text-gray-400">JU-{{ $trx['ref'] }}</td>
                                                        <td class="py-1.5 text-right text-gray-600">
                                                            {{ $trx['debit'] > 0 ? 'Rp ' . number_format($trx['debit'], 0, ',', '.') : '' }}
                                                        </td>
                                                        <td class="py-1.5 text-right text-gray-600">
                                                            {{ $trx['kredit'] > 0 ? 'Rp ' . number_format($trx['kredit'], 0, ',', '.') : '' }}
                                                        </td>
                                                        <td class="py-1.5 text-right font-semibold text-gray-800">
                                                            Rp {{ number_format($trx['saldo'], 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="py-3 text-center text-gray-400 italic">
                                                            Tidak ada transaksi di periode ini.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-400">
                                Tidak ada data akun ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-filament-panels::page>