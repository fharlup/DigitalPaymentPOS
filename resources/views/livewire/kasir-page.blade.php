<div x-data class="p-4">
    
    {{-- 1. Script Midtrans & Listener --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-midtrans', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){
                        // Panggil fungsi PHP paymentSuccess dengan ID Transaksi
                        @this.paymentSuccess(data.trx_id);
                    },
                    onPending: function(result){
                        alert("Menunggu pembayaran...");
                    },
                    onError: function(result){
                        alert("Pembayaran Gagal/Dibatalkan");
                    }
                });
            });
        });
    </script>

    {{-- Header --}}
   {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Kasir</h2>
            <p class="text-xs text-gray-500">Halo, {{ auth()->user()->name }} 👋</p>
        </div>
        
        <div class="flex gap-2">
            <div class="flex items-center gap-1 text-[10px] text-gray-500 bg-gray-100 px-2 py-1 rounded-full border">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Live
            </div>

            <a href="/logout" class="bg-red-50 text-red-500 px-3 py-1 rounded-lg text-xs font-bold border border-red-100 hover:bg-red-100">
                Keluar
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm animate-pulse">
            <p class="font-bold">Sukses!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- 
        LIST PESANAN (Looping) 
        wire:poll.5s artinya halaman ini mengecek data baru ke server setiap 5 detik 
    --}}
    <div wire:poll.5s class="space-y-4">
        
        @forelse ($this->pendingTransaksis as $trx)
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                
                {{-- Header Kartu Pesanan --}}
                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                            <span>👤 {{ $trx->nama_pelanggan }}</span>
                            <span class="bg-orange-100 text-orange-600 text-[10px] px-2 py-0.5 rounded-full border border-orange-200">
                                #{{ $trx->id }}
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            🕒 {{ $trx->created_at->diffForHumans() }} ({{ $trx->created_at->format('H:i') }})
                        </p>
                    </div>
                    <div class="text-right">
                         <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">
                            BELUM LUNAS
                         </span>
                    </div>
                </div>

                {{-- Body (Detail Harga) --}}
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Total Tagihan</span>
                        <span class="text-2xl font-black text-gray-800">
                            Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="grid grid-cols-2 gap-3">
                        
                        <button 
                            wire:click="bayarTunai({{ $trx->id }})"
                            wire:confirm="Konfirmasi pembayaran TUNAI senilai Rp {{ number_format($trx->total_harga) }}?"
                            wire:loading.attr="disabled"
                            class="flex justify-center items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors">
                            
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Bayar Tunai
                        </button>

                        <button 
                            wire:click="processQris({{ $trx->id }})"
                            wire:loading.attr="disabled"
                            class="flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors">
                            
                            <svg wire:loading wire:target="processQris({{ $trx->id }})" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            
                            <span wire:loading.remove wire:target="processQris({{ $trx->id }})">Scan QRIS</span>
                        </button>

                    </div>
                </div>
            </div>

        @empty
            
            {{-- Tampilan Kosong --}}
            <div class="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">🍵</span>
                </div>
                <h3 class="text-lg font-bold text-gray-600">Tidak ada antrian</h3>
                <p class="text-gray-400">Menunggu pesanan masuk...</p>
            </div>

        @endforelse

    </div>
</div>