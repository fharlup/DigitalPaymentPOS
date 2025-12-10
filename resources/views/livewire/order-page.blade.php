<div x-data class="relative bg-gray-50 min-h-screen font-sans">
    
    {{-- Script Midtrans --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('trigger-payment', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){ @this.paymentSuccess(data.trx_id); },
                    onPending: function(result){ alert("Menunggu pembayaran..."); },
                    onError: function(result){ 
                        // Jika error di Midtrans, kirim feedback ke Livewire
                        @this.dispatch('midtrans-error', { message: 'Pembayaran Dibatalkan/Gagal' });
                    }
                });
            });
        });
    </script>

    {{-- ========================================== --}}
    {{--  NOTIFIKASI TOAST (PERBAIKAN VISUAL)       --}}
    {{-- ========================================== --}}
    <div class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[100] w-full max-w-sm px-4 space-y-2 pointer-events-none">
        @if (session()->has('message'))
            <div class="bg-green-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 animate-slide-down pointer-events-auto">
                <div class="bg-white/20 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                <span class="font-bold text-sm">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 animate-shake pointer-events-auto">
                <div class="bg-white/20 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif
        
        {{-- Pesan Validasi Error (Nama Kosong) --}}
        @error('nama_pelanggan')
            <div class="bg-red-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 animate-shake pointer-events-auto">
                 <div class="bg-white/20 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                 <span class="font-bold text-sm">{{ $message }}</span>
            </div>
        @enderror
    </div>

    {{-- MODAL SUKSES ORDER TUNAI --}}
    @if ($successOrderId)
    <div class="fixed inset-0 bg-black bg-opacity-80 z-[90] flex items-center justify-center p-4 backdrop-blur-sm">
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

    <div class="p-4 pb-32">
        {{-- Input Nama --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border mb-6 sticky top-2 z-30
                    @error('nama_pelanggan') border-red-500 ring-2 ring-red-200 @else border-gray-100 @enderror">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Pemesan</label>
            <input type="text" wire:model="nama_pelanggan" placeholder="Siapa nama kamu?" 
                   class="w-full text-xl font-bold border-b-2 outline-none py-2 bg-transparent transition-colors
                          @error('nama_pelanggan') border-red-500 text-red-600 placeholder-red-300 @else border-gray-200 focus:border-orange-500 text-gray-800 placeholder-gray-300 @enderror">
            @error('nama_pelanggan') <p class="text-xs text-red-500 mt-1 font-bold">⚠️ Wajib diisi ya!</p> @enderror
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
                        
                        <div class="h-32 w-full bg-gray-100 overflow-hidden relative">
                            @if ($produk->gambar)
                                <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $produk->nama_produk }}">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-300 text-4xl">🍲</div>
                            @endif
                            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[10px] px-2 py-1 rounded-lg font-medium">
                                Stok: {{ $produk->stok }}
                            </div>
                        </div>

                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2">{{ $produk->nama_produk }}</h3>
                            <p class="text-orange-600 font-black text-sm">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                            <button class="mt-3 w-full bg-orange-50 text-orange-600 text-xs font-bold py-2 rounded-lg hover:bg-orange-500 hover:text-white transition-colors flex items-center justify-center gap-1">
                                + Tambah
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- FLOATING BAR --}}
    @if (!empty($cart) && !$showCartModal)
        <div class="fixed bottom-4 left-4 right-4 max-w-[480px] mx-auto z-40 cursor-pointer animate-slide-up"
             wire:click="$set('showCartModal', true)">
            <div class="bg-gray-900 text-white p-4 rounded-2xl shadow-2xl flex justify-between items-center hover:bg-gray-800 transition-colors border border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-500 text-white font-bold w-10 h-10 flex items-center justify-center rounded-full">
                        {{ count($cart) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-400">Total Estimasi</span>
                        <span class="font-bold text-lg">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 font-bold text-sm text-orange-400">
                    Lihat Keranjang
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL KERANJANG --}}
    @if ($showCartModal && !empty($cart))
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                 wire:click="$set('showCartModal', false)"></div>

            <div class="bg-white w-full max-w-[480px] rounded-t-3xl sm:rounded-3xl p-6 relative z-10 max-h-[90vh] flex flex-col shadow-2xl animate-slide-up">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-black text-gray-800">Keranjang 🛒</h2>
                    <button wire:click="$set('showCartModal', false)" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200">
                        ✖
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto pr-2 space-y-4 mb-6 no-scrollbar">
                    @foreach ($cart as $key => $item)
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                @if (isset($item['gambar']) && $item['gambar'])
                                    <img src="{{ asset('storage/' . $item['gambar']) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">🍲</div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $item['nama'] }}</h4>
                                <p class="text-orange-600 font-bold text-xs">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-1 border border-gray-100">
                                <button wire:click="updateQty({{ $key }}, -1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-orange-600 font-bold">-</button>
                                <span class="text-sm font-bold w-4 text-center">{{ $item['jumlah'] }}</span>
                                <button wire:click="updateQty({{ $key }}, 1)" class="w-7 h-7 flex items-center justify-center bg-orange-500 rounded shadow-sm text-white font-bold">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-4 space-y-4 bg-white">
                    <div class="flex justify-between items-center text-lg">
                        <span class="text-gray-500 font-medium">Total Bayar</span>
                        <span class="font-black text-gray-800 text-2xl">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                    
                    {{-- TOMBOL CHECKOUT --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="checkout('tunai')" wire:loading.attr="disabled" class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-3.5 rounded-xl font-bold text-sm border border-gray-200">
                            Bayar Kasir 💵
                        </button>
                        <button wire:click="checkout('qris')" wire:loading.attr="disabled" class="bg-orange-500 hover:bg-orange-600 text-white py-3.5 rounded-xl font-bold text-sm flex justify-center items-center gap-2 shadow-lg shadow-orange-500/30">
                            <span>Scan QRIS 📱</span>
                            <svg wire:loading wire:target="checkout('qris')" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>