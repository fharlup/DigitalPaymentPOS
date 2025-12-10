<div x-data class="relative bg-gray-50 min-h-screen">
    
    {{-- Script Midtrans --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('trigger-payment', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){ @this.paymentSuccess(data.trx_id); },
                    onPending: function(result){ alert("Menunggu pembayaran..."); },
                    onError: function(result){ alert("Pembayaran Gagal."); }
                });
            });
        });
    </script>

    {{-- MODAL SUKSES ORDER TUNAI --}}
    @if ($successOrderId)
    <div class="fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-8 text-center max-w-sm w-full shadow-2xl animate-bounce-in relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-orange-400 to-red-500"></div>
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-3xl font-black text-gray-800 mb-2">Order Diterima!</h2>
            <p class="text-gray-500 mb-6">Mohon segera lakukan pembayaran di kasir.</p>
            
            <div class="bg-orange-50 border-2 border-orange-100 rounded-2xl p-6 mb-8">
                <p class="text-xs text-orange-400 font-bold uppercase tracking-widest mb-1">Nomor Antrian</p>
                <p class="text-5xl font-black text-orange-600">#{{ $successOrderId }}</p>
            </div>

            <button wire:click="$set('successOrderId', null)" class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-lg">
                Tutup & Pesan Lagi
            </button>
        </div>
    </div>
    @endif

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-3 text-center font-bold sticky top-0 z-40 shadow-md">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-500 text-white p-3 text-center font-bold sticky top-0 z-40 shadow-md">{{ session('error') }}</div>
    @endif

    <div class="p-4 pb-32">
        {{-- Input Nama --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 sticky top-2 z-30">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Pemesan</label>
            <input type="text" wire:model="nama_pelanggan" placeholder="Siapa nama kamu?" 
                   class="w-full text-xl font-bold border-b-2 border-gray-200 focus:border-orange-500 outline-none py-2 text-gray-800 bg-transparent placeholder-gray-300 transition-colors">
        </div>

        {{-- Loop Kategori & Produk --}}
        @foreach ($kategoris as $kategori)
            <h2 class="font-black text-gray-800 text-xl mt-8 mb-4 flex items-center gap-2">
                <span class="w-1 h-6 bg-orange-500 rounded-full"></span>
                {{ $kategori->nama_kategori }}
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                @foreach ($kategori->produks as $produk)
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between active:scale-95 transition-transform duration-100 relative group" 
                         wire:click="addToCart({{ $produk->id }})">
                        
                        {{-- Gambar Produk --}}
                        <div class="h-32 w-full bg-gray-100 overflow-hidden relative">
                            @if ($produk->gambar)
                                <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $produk->nama_produk }}">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-300 text-4xl">🍲</div>
                            @endif
                            
                            {{-- Badge Stok --}}
                            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[10px] px-2 py-1 rounded-lg font-medium">
                                Stok: {{ $produk->stok }}
                            </div>
                        </div>

                        {{-- Info Produk --}}
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2">{{ $produk->nama_produk }}</h3>
                            <p class="text-orange-600 font-black text-sm">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                            
                            <button class="mt-3 w-full bg-orange-50 text-orange-600 text-xs font-bold py-2 rounded-lg hover:bg-orange-500 hover:text-white transition-colors flex items-center justify-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Tambah
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Bottom Sheet Keranjang --}}
    @if (!empty($cart))
        <div class="fixed bottom-0 left-0 right-0 w-full max-w-[480px] mx-auto z-40">
            <div class="bg-gray-900 text-white p-5 rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.2)] space-y-4 animate-slide-up">
                
                <div class="flex justify-between items-end border-b border-gray-700 pb-4">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">{{ count($cart) }} Item di keranjang</p>
                        <p class="font-bold text-2xl tracking-tight">Rp {{ number_format($this->total, 0, ',', '.') }}</p>
                    </div>
                    <button wire:click="$set('cart', [])" class="text-xs text-red-400 hover:text-red-300 underline">Reset</button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="checkout('tunai')" wire:loading.attr="disabled"
                            class="bg-gray-700 hover:bg-gray-600 text-white py-3.5 rounded-xl font-bold text-sm transition-colors border border-gray-600">
                        Bayar Kasir 💵
                    </button>

                    <button wire:click="checkout('qris')" wire:loading.attr="disabled"
                            class="bg-orange-500 hover:bg-orange-600 text-white py-3.5 rounded-xl font-bold text-sm transition-colors flex justify-center items-center gap-2 shadow-lg shadow-orange-500/30">
                        <span>Scan QRIS 📱</span>
                        <svg wire:loading wire:target="checkout('qris')" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>