<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Soto Mbak Eni' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Trik agar tampilan scrollbar sembunyi tapi tetap bisa scroll */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-200 flex justify-center min-h-screen">
    
    <div class="w-full max-w-[480px] bg-white min-h-screen shadow-2xl relative flex flex-col">
        
        <div class="bg-orange-500 text-white p-4 sticky top-0 z-50 shadow-md">
            <h1 class="text-lg font-bold text-center">Soto Mbak Eni</h1>
        </div>

        <div class="flex-1 p-4 pb-24 overflow-y-auto no-scrollbar">
            {{ $slot }}
        </div>

        <div class="fixed bottom-0 w-full max-w-[480px] bg-white border-t border-gray-200 p-2 flex justify-around items-center z-50">
            <a href="/pesan" class="flex flex-col items-center text-gray-600 hover:text-orange-600">
                <span class="text-2xl">🍜</span>
                <span class="text-xs">Menu</span>
            </a>
            <a href="/kasir" class="flex flex-col items-center text-gray-600 hover:text-orange-600">
                <span class="text-2xl">💰</span>
                <span class="text-xs">Kasir</span>
            </a>
        </div>
    </div>

</body>
</html>