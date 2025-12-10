<div x-data class="relative bg-gray-50 min-h-screen font-sans">
    
    {{-- ==================================================== --}}
    {{-- 1. SCRIPT MIDTRANS SNAP (Standar)                    --}}
    {{-- ==================================================== --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('trigger-payment', (data) => {
                // Memanggil Popup Snap Midtrans
                snap.pay(data.token, {
                    // Jika sukses bayar
                    onSuccess: function(result){ 
                        @this.paymentSuccess(data.trx_id); 
                    },
                    // Jika ditutup (pending)
                    onPending: function(result){ 
                        alert("Menunggu pembayaran..."); 
                    },
                    // Jika error
                    onError: function(result){ 
                        @this.dispatch('midtrans-error', { message: 'Pembayaran Dibatalkan' }); 
                    },
                    // Jika ditutup tanpa bayar
                    onClose: function(){ 
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                    }
                });
            });
        });
    </script>

    {{-- ==================================================== --}}
    {{-- 2. NOTIFIKASI TOAST (Melayang di Atas)               --}}
    {{-- ==================================================== --}}
    <div class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[100] w-full max-w-sm px-4 space-y-2 pointer-events-none">
        @if (session()->has('message'))
            <div class="bg-green-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 pointer-events-auto animate-bounce-in">
                <div class="bg-white/20 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                <span class="font-bold text-sm">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 pointer-events-auto animate-shake">
                <div class="bg-white/20 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    {{-- ==================================================== --}}
    {{-- 3. MODAL SUKSES (ORDER TUNAI)                        --}}
    {{-- ==================================================== --}}
    @if ($successOrderId)
    <div class="fixed inset-0 bg-black bg-opacity-80 z-[150] flex items-center justify-center p-4 backdrop-blur-sm">
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

    {{-- ==================================================== --}}
    {{-- 4. KONTEN UTAMA (INPUT NAMA & LIST PRODUK)           --}}
    {{-- ==================================================== --}}
    <div class="p-4 pb-32 max-w-[480px] mx-auto">
        
        {{-- Input Nama --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border mb-6 sticky top-4 z-30 transition-colors duration-300
                    @error('nama_pelanggan') border-red-500 ring-4 ring-red-100 @else border-gray-100 @enderror">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Pemesan</label>
            <input type="text" wire:model.blur="nama_pelanggan" placeholder="Siapa nama kamu?" 
                   class="w-full text-xl font-bold border-b-2 outline-none py-2 bg-transparent transition-colors
                          @error('nama_pelanggan') border-red-500 text-red-600 placeholder-red-300 @else border-gray-200 focus:border-orange-500 text-gray-800 placeholder-gray-300 @enderror">
            @error('nama_pelanggan') <p class="text-xs text-red-500 mt-2 font-bold flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg> Wajib diisi ya!</p> @enderror
        </div>

        {{-- Loop Kategori & Produk --}}
        @foreach ($kategoris as $kategori)
            <h2 class="font-black text-gray-800 text-xl mt-8 mb-4 flex items-center gap-2 pl-1">
                <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span>
                {{ $kategori->nama_kategori }}
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                @foreach ($kategori->produks as $produk)
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between active:scale-95 transition-transform duration-100 relative group cursor-pointer" 
                         wire:click="addToCart({{ $produk->id }})">
                        
                        {{-- Gambar Produk --}}
                        <div class="h-32 w-full bg-gray-100 overflow-hidden relative">
                            @if ($produk->gambar)
                                <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $produk->nama_produk }}">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-300 text-4xl">🍲</div>
                            @endif
                            
                            {{-- Badge Stok --}}
                            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[10px] px-2 py-1 rounded-lg font-medium shadow-sm">
                                Stok: {{ $produk->stok }}
                            </div>
                        </div>

                        {{-- Info Produk --}}
                        <div class="p-3">
                            <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 h-10">{{ $produk->nama_produk }}</h3>
                            <p class="text-orange-600 font-black text-sm">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                            
                            <button class="mt-3 w-full bg-orange-50 text-orange-600 text-xs font-bold py-2.5 rounded-xl hover:bg-orange-500 hover:text-white transition-colors flex items-center justify-center gap-1 group-hover:shadow-md">
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
        
        @if($kategoris->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p>Belum ada menu yang tersedia.</p>
            </div>
        @endif
    </div>

    {{-- ==================================================== --}}
    {{-- 5. FLOATING BAR (RINGKASAN KERANJANG)                --}}
    {{-- ==================================================== --}}
    @if (!empty($cart) && !$showCartModal)
        <div class="fixed bottom-6 left-4 right-4 max-w-[480px] mx-auto z-40 cursor-pointer animate-slide-up"
             wire:click="$set('showCartModal', true)">
            <div class="bg-gray-900 text-white p-4 rounded-2xl shadow-2xl flex justify-between items-center hover:bg-gray-800 transition-colors border border-gray-700 ring-4 ring-gray-900/20">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-500 text-white font-bold w-10 h-10 flex items-center justify-center rounded-full shadow-lg">
                        {{ count($cart) }}
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Total Estimasi</span>
                        <span class="font-black text-lg">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 font-bold text-sm text-orange-400 bg-gray-800 px-3 py-1.5 rounded-lg">
                    Lihat
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================================================== --}}
    {{-- 6. MODAL KERANJANG BELANJA                          --}}
    {{-- ==================================================== --}}
    @if ($showCartModal && !empty($cart))
        <div class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                 wire:click="$set('showCartModal', false)"></div>

            {{-- Konten Modal --}}
            <div class="bg-white w-full max-w-[480px] rounded-t-3xl sm:rounded-3xl p-6 relative z-10 max-h-[85vh] flex flex-col shadow-2xl animate-slide-up">
                
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-2xl font-black text-gray-800">Keranjang 🛒</h2>
                    <button wire:click="$set('showCartModal', false)" class="p-2 bg-gray-50 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- List Item --}}
                <div class="flex-1 overflow-y-auto pr-2 space-y-4 mb-6 no-scrollbar">
                    @foreach ($cart as $key => $item)
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
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
                                <button wire:click="updateQty({{ $key }}, -1)" class="w-8 h-8 flex items-center justify-center bg-white rounded shadow-sm text-gray-400 font-bold hover:text-orange-600 hover:shadow transition-all">-</button>
                                <span class="font-bold w-4 text-center text-gray-700 text-sm">{{ $item['jumlah'] }}</span>
                                <button wire:click="updateQty({{ $key }}, 1)" class="w-8 h-8 flex items-center justify-center bg-orange-500 rounded shadow-sm text-white font-bold hover:bg-orange-600 hover:shadow-lg transition-all">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer Modal --}}
                <div class="border-t border-gray-100 pt-4 space-y-4 bg-white">
                    <div class="flex justify-between items-center text-lg">
                        <span class="text-gray-500 font-medium text-sm">Total Pembayaran</span>
                        <span class="font-black text-gray-800 text-2xl">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Tombol Bayar Tunai --}}
                        <button wire:click="checkout('tunai')" wire:loading.attr="disabled" 
                                class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-4 rounded-xl font-bold text-sm border border-gray-200 transition-colors flex flex-col items-center justify-center gap-1">
                            <span>Bayar Kasir</span>
                            <span class="text-[10px] text-gray-400 font-normal">(Tunai)</span>
                        </button>

                        {{-- Tombol Scan QRIS (Memanggil Snap) --}}
                        <button wire:click="checkout('qris')" wire:loading.attr="disabled" 
                                class="bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-xl font-bold text-sm flex flex-col items-center justify-center gap-1 shadow-lg shadow-orange-200 transition-transform active:scale-95">
                            <div class="flex items-center gap-2">
                                <span>Bayar Online</span>
                                <svg wire:loading wire:target="checkout('qris')" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            <span class="text-[10px] text-orange-200 font-normal">(QRIS / Gopay)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>