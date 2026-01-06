<div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-20">
    
    {{-- Header Sederhana --}}
    <div class="bg-white p-4 shadow-sm sticky top-0 z-10">
        <h1 class="font-bold text-xl text-orange-600">Soto Mbak Eni</h1>
        <p class="text-xs text-gray-500">Self Service Order</p>
    </div>

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-4 rounded">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form Data Diri --}}
    <div class="p-4 bg-white m-4 rounded-xl shadow-sm space-y-3">
        <div>
            <label class="text-xs font-bold text-gray-500 uppercase">Nama Anda</label>
            <input type="text" wire:model="nama_pelanggan" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
            @error('nama_pelanggan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="text-xs font-bold text-gray-500 uppercase">Nomor Meja</label>
            <select wire:model="no_meja" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                <option value="">-- Pilih Meja --</option>
                @foreach(range(1, 15) as $no)
                    <option value="Meja {{ $no }}">Meja {{ $no }}</option>
                @endforeach
            </select>
            @error('no_meja') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- List Menu (Contoh) --}}
    <div class="p-4 grid grid-cols-2 gap-3">
        @foreach($produks as $produk)
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">{{ $produk->nama_produk }}</h3>
                    <p class="text-orange-600 font-bold text-xs">Rp {{ number_format($produk->harga) }}</p>
                </div>
                <button wire:click="addToCart({{ $produk->id }})" class="mt-2 w-full bg-orange-100 text-orange-600 text-xs font-bold py-1 rounded hover:bg-orange-200">
                    + Pesan
                </button>
            </div>
        @endforeach
    </div>

    {{-- Footer Cart & Checkout --}}
    @if(count($cart) > 0)
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-lg rounded-t-2xl z-20 max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <span class="font-bold text-gray-700">Total Keranjang</span>
                <span class="font-black text-xl text-gray-800">
                    Rp {{ number_format(collect($cart)->sum(fn($i) => $i['harga'] * $i['qty'])) }}
                </span>
            </div>

            <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Metode Pembayaran</h3>
            <div class="grid grid-cols-2 gap-3">
                {{-- Tombol Tunai --}}
                <button wire:click="checkout('tunai')" wire:loading.attr="disabled"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-xl flex items-center justify-center gap-2">
                    💵 Tunai
                </button>

                {{-- Tombol QRIS --}}
                <button wire:click="checkout('qris')" wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="checkout('qris')">📱 Scan QRIS</span>
                    <span wire:loading wire:target="checkout('qris')" class="animate-spin">↻ Loading...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- =========================================== --}}
    {{-- MODAL QRIS (POPUP)                          --}}
    {{-- =========================================== --}}
    @if($showQrisModal)
        <div wire:poll.3s="checkPaymentStatus" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm text-center overflow-hidden animate-bounce-in">
                
                <div class="bg-blue-600 p-4">
                    <h3 class="text-white font-bold text-lg">Scan / Copy Link</h3>
                    <p class="text-blue-100 text-xs">Silakan bayar untuk menyelesaikan pesanan</p>
                </div>

                <div class="p-6">
                    {{-- Gambar QR --}}
                    @if($qrisImageUrl)
                        <div class="bg-white p-2 border-2 border-dashed border-gray-300 rounded-xl inline-block mb-4">
                            <img src="{{ $qrisImageUrl }}" alt="QRIS" class="w-48 h-48 object-contain">
                        </div>

                        {{-- INPUT COPY LINK UNTUK SIMULATOR --}}
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-left mb-4">
                            <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">
                                Link Gambar (Untuk Simulator):
                            </label>
                            <div class="flex gap-2">
                                <input type="text" value="{{ $qrisImageUrl }}" readonly 
                                    class="w-full text-xs border border-gray-300 rounded px-2 py-2 bg-white text-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="qrisLink">
                                
                                <button onclick="copyLink()" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1 rounded transition-colors whitespace-nowrap">
                                    Copy
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 italic">
                                *Paste link ini di "QR Code Image Url" Simulator Midtrans
                            </p>
                        </div>
                    @else
                        <div class="w-48 h-48 flex items-center justify-center text-gray-400 mx-auto">
                            <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button wire:click="$set('showQrisModal', false)" class="text-red-500 text-xs font-bold hover:underline">
                            Batalkan / Tutup
                        </button>
                    </div>
                </div>
                
                {{-- Progress Bar --}}
                <div class="h-1 w-full bg-gray-100 overflow-hidden">
                    <div class="h-full bg-blue-500 w-1/2 animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Script Javascript untuk Copy --}}
        <script>
            function copyLink() {
                var copyText = document.getElementById("qrisLink");
                
                // Select text field
                copyText.select();
                copyText.setSelectionRange(0, 99999); // Untuk Mobile

                // Copy ke clipboard
                navigator.clipboard.writeText(copyText.value);

                // Feedback visual sederhana
                alert("Link berhasil dicopy! Silakan paste di Simulator Midtrans.");
            }
        </script>
    @endif
</div>