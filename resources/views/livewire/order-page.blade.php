<div x-data class="relative">
    
    {{-- 1. Script Midtrans Wajib --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    {{-- 2. Script Listener Livewire --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('trigger-payment', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){
                        // Panggil fungsi PHP paymentSuccess
                        @this.paymentSuccess(data.trx_id);
                        alert("Pembayaran Berhasil!");
                    },
                    onPending: function(result){
                        alert("Silakan selesaikan pembayaran di Simulator.");
                    },
                    onError: function(result){
                        alert("Pembayaran Gagal.");
                    }
                });
            });
        });
    </script>

    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center font-bold shadow-lg">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center font-bold shadow-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-4 rounded-xl shadow-sm border mb-4 sticky top-1 z-30">
        <label class="text-xs font-bold text-gray-500 uppercase">Nama Pemesan</label>
        <input type="text" wire:model="nama_pelanggan" placeholder="Contoh: Budi" 
               class="w-full text-lg font-bold border-b-2 border-orange-200 focus:border-orange-500 outline-none py-1 text-gray-800 bg-transparent placeholder-gray-300">
    </div>

    @foreach ($kategoris as $kategori)
        {{-- ... Kode List Menu kamu yang sebelumnya tetap disini ... --}}
        <h2 class="font-bold text-gray-700 text-lg mt-4 mb-2">{{ $kategori->nama_kategori }}</h2>
        <div class="grid grid-cols-2 gap-3">
            @foreach ($kategori->produks as $produk)
                <div class="bg-white border rounded-lg p-3 shadow-sm flex flex-col justify-between active:scale-95 transition-transform" wire:click="addToCart({{ $produk->id }})">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $produk->nama_produk }}</h3>
                        <p class="text-orange-600 font-bold text-sm">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                    </div>
                    <button class="mt-2 w-full bg-orange-100 text-orange-600 text-xs py-1 rounded hover:bg-orange-200">
                        + Tambah
                    </button>
                </div>
            @endforeach
        </div>
    @endforeach

    @if (!empty($cart))
        <div class="fixed bottom-16 left-0 right-0 w-full max-w-[480px] mx-auto p-4 z-40">
            <div class="bg-gray-900 text-white p-5 rounded-2xl shadow-2xl space-y-4">
                
                {{-- Info Total --}}
                <div class="flex justify-between items-end border-b border-gray-700 pb-3">
                    <div>
                        <p class="text-xs text-gray-400">{{ count($cart) }} Item di keranjang</p>
                        <p class="font-bold text-2xl">Rp {{ number_format($this->total, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Tombol Aksi (Dua Pilihan) --}}
                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="checkout('tunai')" 
                            class="bg-gray-700 hover:bg-gray-600 text-white py-3 rounded-xl font-bold text-sm">
                        Bayar di Kasir
                    </button>

                    <button wire:click="checkout('qris')" wire:loading.attr="disabled"
                            class="bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold text-sm flex justify-center items-center gap-2">
                        <span>Scan QRIS</span>
                        <svg wire:loading wire:target="checkout('qris')" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>