<x-filament-panels::page>
    {{-- FORM FILTER --}}
    {{ $this->form }}
    
    <div class="flex justify-end mt-4">
        <x-filament::button wire:click="filter">
            Tampilkan Data
        </x-filament::button>
    </div>

    <hr class="my-6 border-gray-200">

    {{-- KONTEN LAPORAN --}}
    @if($selectedAkun)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            {{-- Header Laporan --}}
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">{{ $selectedAkun->nama_akun }} ({{ $selectedAkun->kode_akun }})</h2>
                <p class="text-sm text-gray-500">
                    Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('d M Y') }}
                </p>
                <span class="inline-block mt-2 px-3 py-1 text-xs font-bold rounded-full {{ $selectedAkun->tipe == 'debit' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                    Saldo Normal: {{ strtoupper($selectedAkun->tipe) }}
                </span>
            </div>

            {{-- Tabel --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">No. Ref</th>
                            <th class="px-6 py-3">Keterangan</th>
                            <th class="px-6 py-3 text-right text-emerald-600">Debit</th>
                            <th class="px-6 py-3 text-right text-red-600">Kredit</th>
                            <th class="px-6 py-3 text-right bg-gray-50">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        {{-- BARIS 1: SALDO AWAL --}}
                        <tr class="bg-yellow-50 font-medium">
                            <td class="px-6 py-3" colspan="3">Saldo Awal</td>
                            <td class="px-6 py-3 text-right">-</td>
                            <td class="px-6 py-3 text-right">-</td>
                            <td class="px-6 py-3 text-right font-bold">
                                Rp {{ number_format($saldoAwal, 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- VARIABLE UNTUK RUNNING BALANCE --}}
                        @php
                            $currentSaldo = $saldoAwal;
                            $isDebit = $selectedAkun->tipe == 'debit';
                        @endphp

                        {{-- LOOP TRANSAKSI --}}
                        @forelse($ledgerData as $row)
                            @php
                                // Hitung Saldo Berjalan
                                if ($isDebit) {
                                    $currentSaldo = $currentSaldo + $row->debit - $row->kredit;
                                } else {
                                    $currentSaldo = $currentSaldo + $row->kredit - $row->debit;
                                }
                            @endphp

                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->jurnal->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-3 text-blue-600">
                                    #{{ $row->jurnal->transaksi_id ?? $row->jurnal->id }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $row->jurnal->keterangan }}
                                </td>
                                <td class="px-6 py-3 text-right font-mono">
                                    {{ $row->debit > 0 ? number_format($row->debit) : '-' }}
                                </td>
                                <td class="px-6 py-3 text-right font-mono">
                                    {{ $row->kredit > 0 ? number_format($row->kredit) : '-' }}
                                </td>
                                <td class="px-6 py-3 text-right font-bold font-mono bg-gray-50">
                                    Rp {{ number_format($currentSaldo, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    Tidak ada transaksi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- FOOTER TOTAL --}}
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-200">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right uppercase">Total Mutasi & Saldo Akhir</td>
                            <td class="px-6 py-3 text-right text-emerald-600">
                                {{ number_format($totalDebit) }}
                            </td>
                            <td class="px-6 py-3 text-right text-red-600">
                                {{ number_format($totalKredit) }}
                            </td>
                            <td class="px-6 py-3 text-right bg-gray-200 text-gray-900 text-base">
                                Rp {{ number_format($currentSaldo, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-dashed border-gray-300">
            <p class="text-gray-500">Silakan pilih akun dan periode tanggal lalu klik "Tampilkan Data".</p>
        </div>
    @endif
</x-filament-panels::page>