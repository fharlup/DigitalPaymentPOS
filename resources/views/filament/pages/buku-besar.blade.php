<x-filament-panels::page>
    
    {{-- FORM FILTER --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-end gap-4">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="mb-4">
                 <x-filament::button wire:click="filter" class="bg-gray-800 hover:bg-gray-700 text-white">
                    <x-heroicon-m-funnel class="w-4 h-4 inline mr-1"/> Filter
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- KONTEN LAPORAN --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        {{-- HEADER COKLAT (Mirip Gambar) --}}
        <div class="p-6 bg-[#3E2F29] text-white">
            <h2 class="text-xl font-bold uppercase tracking-wide">KEDAI KOPI AMPUH</h2>
            <p class="text-gray-300 text-sm font-medium mt-1">Buku Besar</p>
            <p class="text-gray-400 text-xs mt-1">
                Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
            </p>
        </div>

        {{-- INFO BAR --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <span class="text-sm text-gray-600">Total akun dengan data: <strong>{{ count($reportData) }}</strong></span>
            <span class="text-xs text-blue-400 font-medium">Tipe yang ada: Aset, Pendapatan, Beban</span>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">KODE</th>
                        <th class="px-6 py-4">NAMA AKUN</th>
                        <th class="px-6 py-4 text-center">TIPE</th>
                        <th class="px-6 py-4 text-right">SALDO AWAL</th>
                        <th class="px-6 py-4 text-right">DEBIT</th>
                        <th class="px-6 py-4 text-right">KREDIT</th>
                        <th class="px-6 py-4 text-right">SALDO AKHIR</th>
                        <th class="px-6 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reportData as $row)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            {{-- KODE --}}
                            <td class="px-6 py-4 font-mono text-gray-600">
                                {{ $row['kode'] }}
                            </td>
                            
                            {{-- NAMA AKUN --}}
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $row['nama'] }}
                            </td>
                            
                            {{-- TIPE (Badge) --}}
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeColor = match(strtolower($row['tipe'])) {
                                        'aset', 'harta' => 'bg-green-100 text-green-700',
                                        'beban', 'biaya' => 'bg-red-100 text-red-700',
                                        'pendapatan' => 'bg-blue-100 text-blue-700',
                                        'modal' => 'bg-purple-100 text-purple-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase {{ $badgeColor }}">
                                    {{ $row['tipe'] }}
                                </span>
                            </td>

                            {{-- SALDO AWAL --}}
                            <td class="px-6 py-4 text-right text-gray-500">
                                Rp {{ number_format($row['saldo_awal'], 0, ',', '.') }}
                            </td>

                            {{-- DEBIT --}}
                            <td class="px-6 py-4 text-right text-gray-500">
                                Rp {{ number_format($row['debit'], 0, ',', '.') }}
                            </td>

                            {{-- KREDIT --}}
                            <td class="px-6 py-4 text-right text-gray-500">
                                Rp {{ number_format($row['kredit'], 0, ',', '.') }}
                            </td>

                            {{-- SALDO AKHIR (TEBAL) --}}
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}
                            </td>

                            {{-- AKSI (TOMBOL MATA) --}}
                            <td class="px-6 py-4 text-center">
                                <button type="button" class="text-cyan-500 hover:text-cyan-700 bg-cyan-50 hover:bg-cyan-100 p-2 rounded-lg transition-all">
                                    <x-heroicon-m-eye class="w-4 h-4"/>
                                </button>
                            </td>
                        </tr>
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