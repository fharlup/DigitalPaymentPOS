<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaksi->id }} - Soto Mbak Eni</title>
    <script src="https://cdn.tailwindcss.com"></script> 
    <style>
        body { background-color: #f3f4f6; font-family: sans-serif; }
        .ticket {
            background: white;
            max-width: 400px;
            margin: 20px auto;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        .ticket::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 10px;
            background: radial-gradient(circle, transparent 50%, white 50%);
            background-size: 20px 20px;
            background-position: center bottom;
        }
    </style>
</head>
<body class="p-4 flex items-center justify-center min-h-screen">

    <div class="ticket w-full">
        <div class="bg-orange-600 p-6 text-center text-white">
            <h1 class="text-2xl font-black mb-1 uppercase">Soto Mbak Eni</h1>
            <p class="text-orange-100 text-sm font-medium">Rasa Legendaris Sejak Lama</p>
            
            <div class="mt-4">
                <div class="inline-block bg-white text-orange-600 px-4 py-1 rounded-full font-bold text-sm shadow">
                    ✓ LUNAS
                </div>
            </div>
        </div>

        <div class="p-6 pb-12">
            <div class="text-center text-gray-500 text-xs mb-6 space-y-1">
                <p>{{ $transaksi->created_at->format('d M Y, H:i') }}</p>
                <p>Order ID: #{{ $transaksi->id }}</p>
            </div>

            <div class="flex justify-between border-b border-gray-100 pb-2 mb-4">
                <span class="text-gray-500 text-sm">Pelanggan</span>
                <span class="font-bold text-gray-800">{{ $transaksi->nama_pelanggan }} (Meja {{ $transaksi->no_meja }})</span>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Rincian Menu</p>
                
                <div class="space-y-3">
                    @foreach($transaksi->detailTransaksi as $item)
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-4">
                            <p class="text-sm font-bold text-gray-800">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $item->jumlah }} x Rp {{ number_format(($item->subtotal / $item->jumlah), 0, ',', '.') }}
                            </p>
                        </div>
                        <p class="text-sm font-bold text-gray-800">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t-2 border-dashed border-gray-200 my-4"></div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div class="flex justify-between items-center text-lg font-black text-gray-900">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-gray-500 mt-1 uppercase font-bold">
                    <span>Metode Pembayaran</span>
                    <span>{{ $transaksi->metode_pembayaran ?? 'TUNAI' }}</span>
                </div>
            </div>
            
            <div class="mt-6 text-center">
                 <a href="https://wa.me/?text=Struk%20Soto%20Mbak%20Eni%0AOrder%20ID:%20{{ $transaksi->id }}%0ATotal:%20Rp%20{{ number_format($transaksi->total_harga) }}%0A%0ALihat%20disini:%20{{ route('struk.digital', $transaksi->id) }}" 
                   target="_blank"
                   class="block w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold text-sm shadow transition">
                   📲 Simpan / Kirim WhatsApp
                </a>
            </div>
        </div>
    </div>

</body>
</html>