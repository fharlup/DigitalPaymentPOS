<div class="flex flex-col justify-center min-h-[80vh] p-6 bg-white">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-orange-600 mb-2">Soto Mbak Eni</h1>
        <p class="text-gray-500 text-sm">Login khusus Karyawan & Admin</p>
    </div>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full border-2 border-gray-200 p-3 rounded-xl outline-none focus:border-orange-500 font-bold text-gray-700">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password</label>
            <input type="password" wire:model="password" class="w-full border-2 border-gray-200 p-3 rounded-xl outline-none focus:border-orange-500 font-bold text-gray-700">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-orange-700 transition-colors">
            MASUK
            <span wire:loading class="ml-2 animate-spin">⏳</span>
        </button>
    </form>
    
    <div class="mt-8 text-center">
        <a href="/pesan" class="text-xs text-gray-400 hover:text-orange-500">
            ← Kembali ke Menu Pelanggan
        </a>
    </div>
</div>