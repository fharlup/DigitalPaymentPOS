<div x-data class="min-h-screen bg-gray-50 pb-20">
    
    {{-- Script Midtrans --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-midtrans', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){ @this.paymentSuccess(data.trx_id); },
                    onPending: function(result){ alert("Menunggu pembayaran..."); },
                    onError: function(result){ alert("Pembayaran Gagal"); }
                });
            });
        });
    </script>

    {{-- HEADER: Dibuat Full Width & Lebih Rapi --}}
    <div class="bg-white border-b sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-500 text-white p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-none">Kasir & Dapur</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Soto Mbak Eni</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-green-50 px-3 py-1.5 rounded-full border border-green-100">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-bold text-green-700 uppercase tracking-wide">Realtime Mode</span>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTAINER UTAMA: Max Width 7xl agar lega --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm animate-fade-in-down">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium">{{ session('message') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700 font-bold">&times;</button>
            </div>
        @endif

        {{-- GRID SYSTEM: Responsive (1 kolom HP -> 4 kolom Desktop) --}}
        <div wire:poll.5s class="grid grid-cols-2">
            
            @forelse ($this->transaksis as $trx)
                
                {{-- KARTU PESANAN --}}
                <div class="relative flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 overflow-hidden group">
                    
                    {{-- Status Bar di atas kartu (Warna Indikator) --}}
                    <div class="h-1.5 w-full {{ $trx->status === 'paid' ? 'bg-blue-500' : 'bg-orange-400' }}"></div>

                    <div class="p-5 flex flex-col h-full">
                        
                        {{-- Header Kartu --}}
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800 line-clamp-1" title="{{ $trx->nama_pelanggan }}">
                                    {{ $trx->nama_pelanggan }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1 text-sm font-medium text-gray-500">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $trx->no_meja }}
                                </div>
                            </div>
                        </div>

                        {{-- Total Harga --}}
                        <div class="mb-6 pt-4 border-t border-dashed border-gray-100">
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Total Tagihan</p>
                            <p class="text-2xl font-black text-gray-800">
                                <span class="text-lg text-gray-400 font-medium mr-0.5">Rp</span>{{ number_format($trx->total_harga, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Bagian Tombol Aksi (Sticky di bawah) --}}
                        <div class="mt-auto">
                            @if($trx->status === 'pending')
                                {{-- MODE BAYAR: Tombol Grid 2 Kolom --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <button wire:click="bayarTunai({{ $trx->id }})" 
                                            wire:confirm="Terima Tunai Rp {{ number_format($trx->total_harga) }}?" 
                                            wire:loading.attr="disabled"
                                            class="flex flex-col items-center justify-center gap-1 py-3 px-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 rounded-xl transition-colors group-hover:border-emerald-300">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span class="text-xs font-bold">Tunai</span>
                                    </button>

                                    <button wire:click="processQris({{ $trx->id }})" 
                                            wire:loading.attr="disabled"
                                            class="flex flex-col items-center justify-center gap-1 py-3 px-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-xl transition-colors group-hover:border-indigo-300 relative">
                                        
                                        <div wire:loading wire:target="processQris({{ $trx->id }})" class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-xl">
                                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </div>

                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        <span class="text-xs font-bold">QRIS</span>
                                    </button>
                                </div>
                            @else
                                {{-- MODE SELESAI: Tombol Full Width --}}
                                <button wire:click="markAsDone({{ $trx->id }})" 
                                        wire:confirm="Pastikan pesanan Meja {{ $trx->no_meja }} sudah diantar?"
                                        class="w-full group/btn flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl shadow-md shadow-blue-200 transition-all active:scale-95">
                                    <span class="text-sm font-bold">Selesai Diantar</span>
                                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                                <p class="text-center text-[10px] text-gray-400 mt-2">
                                    Klik jika makanan sudah sampai
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            @empty
                {{-- State Kosong --}}
                <div class="col-span-full flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-gray-200 text-gray-400">
                    <div class="bg-gray-50 p-6 rounded-full mb-4">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <p class="text-lg font-medium text-gray-500">Belum ada antrian pesanan</p>
                    <p class="text-sm text-gray-400">Pesanan baru akan muncul di sini secara otomatis</p>
                </div>
            @endforelse

        </div>
    </div>
</div>