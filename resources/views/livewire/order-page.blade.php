<div x-data class="relative bg-gray-50 min-h-screen font-sans">

    {{-- HEADER & TABS --}}
    <div class="sticky top-0 z-40 bg-white shadow-sm">
        <div class="px-4 py-3 flex justify-between items-center border-b border-gray-100">
            <h1 class="text-xl font-black text-orange-600 tracking-tight">SOTO MBAK ENI</h1>
            <div class="text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded">Meja #12</div>
        </div>
        <div class="flex overflow-x-auto whitespace-nowrap no-scrollbar border-b border-gray-200">
            @foreach ($kategoris as $kategori)
                <a href="#kategori-{{ $kategori->id }}" class="px-5 py-3 text-sm font-bold text-gray-600 uppercase border-b-2 border-transparent hover:text-orange-600 hover:border-orange-500 transition-colors snap-center">
                    {{ $kategori->nama_kategori }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="p-4 pb-32 max-w-[480px] mx-auto">
        
        {{-- Input Nama --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Nama Pemesan</label>
            <input type="text" wire:model.blur="nama_pelanggan" placeholder="Masukkan nama kamu..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm font-bold text-gray-800 focus:outline-none focus:border-orange-500 focus:bg-white transition-colors @error('nama_pelanggan') border-red-500 @enderror">
            @error('nama_pelanggan') <span class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
        </div>

        @foreach ($kategoris as $kategori)
            <div id="kategori-{{ $kategori->id }}" class="scroll-mt-36 mb-8">
                {{-- Judul Kategori --}}
                <h2 class="font-black text-gray-800 text-lg mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span>
                    {{ $kategori->nama_kategori }}
                </h2>

                {{-- GRID 2 KOLOM --}}
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($kategori->produks as $produk)
                        <div wire:click="openDetail({{ $produk->id }})" 
                             class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between active:scale-95 transition-transform duration-100 relative group cursor-pointer">
                            
                            {{-- GAMBAR (DI ATAS) --}}
                            <div class="h-32 w-full bg-gray-100 overflow-hidden relative">
                                @if ($produk->gambar)
                                    <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-4xl">🍲</div>
                                @endif
                                
                                {{-- Badge Stok --}}
                                @if($produk->stok <= 0)
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-[1px]">
                                        <span class="text-white text-[10px] font-bold px-2 py-1 bg-red-600 rounded shadow-sm">HABIS</span>
                                    </div>
                                @else
                                    <div class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded-md font-medium">
                                        {{ $produk->stok }} Porsi
                                    </div>
                                @endif
                            </div>

                            {{-- INFO (DI BAWAH) --}}
                            <div class="p-3 flex flex-col flex-1">
                                <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2 h-10">
                                    {{ $produk->nama_produk }}
                                </h3>
                                
                                <div class="mt-auto flex justify-between items-center pt-2">
                                    <p class="text-orange-600 font-black text-sm">
                                        Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                    </p>
                                    
                                    {{-- Tombol Quick Add --}}
                                    @if($produk->stok > 0)
                                        <button wire:click.stop="addToCart({{ $produk->id }})" 
                                                class="w-7 h-7 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center hover:bg-orange-500 hover:text-white transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- FLOATING BAR (KUNING) --}}
    @if (!empty($cart) && !$showCartModal)
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-yellow-400 p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.1)] cursor-pointer animate-slide-up"
             wire:click="$set('showCartModal', true)">
            <div class="max-w-[480px] mx-auto flex justify-between items-center text-black">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">Total Pembayaran</span>
                    <span class="text-xl font-black">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center gap-3 bg-black text-yellow-400 px-5 py-3 rounded-xl font-bold text-sm">
                    <span>KERANJANG</span>
                    <div class="bg-yellow-400 text-black w-6 h-6 flex items-center justify-center rounded-full text-xs font-black shadow-sm">{{ count($cart) }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL PRODUK --}}
    @if ($showDetailModal && $selectedProduct)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="closeDetail"></div>
            <div class="bg-white w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl relative z-10 animate-bounce-in flex flex-col max-h-[90vh]">
                <div class="h-64 bg-gray-200 relative shrink-0">
                    <button wire:click="closeDetail" class="absolute top-4 right-4 bg-white/50 backdrop-blur p-2 rounded-full hover:bg-white text-gray-800 transition-colors z-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    @if ($selectedProduct->gambar)
                        <img src="{{ asset('storage/' . $selectedProduct->gambar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-6xl">🍲</div>
                    @endif
                </div>
                <div class="p-6 flex-1 overflow-y-auto">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="text-2xl font-black text-gray-800 uppercase leading-none">{{ $selectedProduct->nama_produk }}</h2>
                        <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-1 rounded-md">Stok: {{ $selectedProduct->stok }}</span>
                    </div>
                    <p class="text-2xl font-black text-orange-600 mb-4">Rp {{ number_format($selectedProduct->harga, 0, ',', '.') }}</p>
                    <div class="space-y-2">
                        <h4 class="font-bold text-gray-400 text-xs uppercase tracking-wider">Deskripsi</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">Nikmati kelezatan {{ $selectedProduct->nama_produk }} yang dibuat dengan bahan-bahan pilihan.</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    @if($selectedProduct->stok > 0)
                        <button wire:click="addToCart({{ $selectedProduct->id }}); closeDetail()" class="w-full bg-orange-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-orange-700 active:scale-95 transition-all">TAMBAH KE PESANAN</button>
                    @else
                        <button disabled class="w-full bg-gray-300 text-gray-500 py-4 rounded-xl font-bold text-lg cursor-not-allowed">STOK HABIS</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL KERANJANG --}}
    @if ($showCartModal && !empty($cart))
        <div class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" wire:click="$set('showCartModal', false)"></div>
            <div class="bg-white w-full max-w-[480px] rounded-t-3xl sm:rounded-3xl p-6 relative z-10 max-h-[85vh] flex flex-col shadow-2xl animate-slide-up">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Pesanan Kamu</h2>
                    <button wire:click="$set('showCartModal', false)" class="p-2 bg-gray-50 rounded-full hover:bg-gray-100"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="flex-1 overflow-y-auto pr-2 space-y-6 mb-6 no-scrollbar">
                    @foreach ($cart as $key => $item)
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200">
                                @if (isset($item['gambar']) && $item['gambar']) <img src="{{ asset('storage/' . $item['gambar']) }}" class="w-full h-full object-cover"> @else <div class="w-full h-full flex items-center justify-center text-gray-300">🍲</div> @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 text-sm uppercase mb-1">{{ $item['nama'] }}</h4>
                                <p class="text-orange-600 font-bold text-sm">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden h-9">
                                <button wire:click="updateQty({{ $key }}, -1)" class="w-9 h-full flex items-center justify-center bg-gray-50 font-bold">-</button>
                                <span class="w-10 h-full flex items-center justify-center font-bold text-sm border-l border-r border-gray-300">{{ $item['jumlah'] }}</span>
                                <button wire:click="updateQty({{ $key }}, 1)" class="w-9 h-full flex items-center justify-center bg-orange-50 font-bold">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 pt-4 space-y-4 bg-white">
                    <div class="flex justify-between items-center"><span class="text-gray-500 font-bold text-sm">Total</span><span class="font-black text-gray-900 text-2xl">Rp {{ number_format($this->total, 0, ',', '.') }}</span></div>
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="checkout('tunai')" wire:loading.attr="disabled" class="bg-gray-100 text-gray-900 py-3.5 rounded-xl font-bold text-sm uppercase">Bayar Tunai</button>
                        <button wire:click="checkout('qris')" wire:loading.attr="disabled" class="bg-yellow-400 text-black py-3.5 rounded-xl font-bold text-sm uppercase flex justify-center items-center gap-2"><span>Bayar Online</span></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- SCRIPT & TOAST --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('trigger-payment', (data) => {
                snap.pay(data.token, {
                    onSuccess: function(result){ @this.paymentSuccess(data.trx_id); },
                    onPending: function(result){ alert("Menunggu pembayaran..."); },
                    onError: function(result){ @this.dispatch('midtrans-error', { message: 'Pembayaran Dibatalkan' }); },
                    onClose: function(){ alert('Anda menutup popup tanpa menyelesaikan pembayaran'); }
                });
            });
        });
    </script>
    <div class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[150] w-full max-w-sm px-4 space-y-2 pointer-events-none">
        @if (session()->has('message')) <div class="bg-green-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 pointer-events-auto animate-bounce-in"><span class="font-bold text-sm">{{ session('message') }}</span></div> @endif
        @if (session()->has('error')) <div class="bg-red-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 pointer-events-auto animate-shake"><span class="font-bold text-sm">{{ session('error') }}</span></div> @endif
    </div>

</div>