<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Rating Predictor (KNN Regressor)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen">

    <nav class="bg-gray-800 p-4 shadow-lg border-b border-gray-700">
        <div class="container mx-auto text-center">
            <a href="/" class="text-2xl font-bold text-blue-500 tracking-wider">GAME<span class="text-white">PREDICTOR</span></a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-6xl">
        
        <div class="bg-gray-800 p-8 rounded-xl shadow-2xl mb-12 border border-gray-700">
            <h1 class="text-3xl font-bold mb-2 text-center">SIMULASI SPESIFIKASI GAME</h1>
            <p class="text-gray-400 mb-8 text-center text-sm">Masukkan taksiran performa game Anda untuk memprediksi nilai rating pasarnya menggunakan KNN Regressor.</p>
            
            <form action="/" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-blue-400 border-b border-gray-700 pb-1">Metriks Pemain</h3>
                        
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Target Total Pemain (Plays)</label>
                            <input type="text" name="plays" placeholder="Contoh: 15K atau 15000" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-white focus:outline-none focus:border-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Pemain Aktif Saat Ini (Playing)</label>
                            <input type="text" name="playing" placeholder="Contoh: 3.5K atau 3500" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-white focus:outline-none focus:border-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Target Daftar Simpanan (Backlogs)</label>
                            <input type="text" name="backlogs" placeholder="Contoh: 4.6K atau 4600" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-white focus:outline-none focus:border-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Target Daftar Keinginan (Wishlist)</label>
                            <input type="text" name="wishlist" placeholder="Contoh: 5K atau 5000" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-white focus:outline-none focus:border-blue-500 transition" required>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-blue-400 border-b border-gray-700 pb-1 mb-4">Pilih Genre (Bisa Lebih dari 1)</h3>
                        <div class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto p-2 bg-gray-900 rounded border border-gray-700">
                            @php
                                $genres = ['Adventure', 'RPG', 'Brawler', 'Indie', 'Platform', 'Simulator', 'Strategy', 'Turn Based Strategy', 'Shooter', 'Puzzle', 'Arcade'];
                            @endphp
                            @foreach($genres as $g)
                            <label class="flex items-center space-x-3 bg-gray-800 p-2 rounded border border-gray-700 hover:border-blue-500 cursor-pointer transition">
                                <input type="checkbox" name="genres[]" value="{{ $g }}" class="w-4 h-4 text-blue-600 bg-gray-900 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="text-sm text-gray-200">{{ $g }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>
                
                <div class="text-center mt-8 flex justify-center gap-4">
                    <a href="/" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-full transition shadow-lg">Reset</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-full transition shadow-lg">Prediksi Rating</button>
                </div>
            </form>
        </div>

        @if(isset($dataHasil) && $dataHasil['status'] == 'success')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <h3 class="text-xl font-bold mb-4 text-blue-400 border-b border-gray-700 pb-2">Hasil Analisis KNN</h3>
                    <div class="bg-gray-800 p-6 rounded-lg border-2 border-green-500 shadow-xl text-center">
                        <span class="block text-sm text-gray-400 uppercase tracking-widest mb-2">Prediksi Rating Akhir</span>
                        <div class="inline-block text-5xl font-mono font-bold bg-gray-900 text-green-400 px-6 py-3 rounded-xl border border-gray-700 shadow-inner">
                            {{ $dataHasil['predicted_rating'] }} <span class="text-gray-500 text-lg">/ 5.0</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-4 leading-relaxed">Nilai ini didapatkan dari rata-rata rating asli 5 game terdekat di dataset yang memiliki kesamaan sebaran interaksi pengguna.</p>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-xl font-bold mb-4 text-purple-400 border-b border-gray-700 pb-2">5 Game Tetangga Terdekat (Referensi Pembanding)</h3>
                    <div class="flex flex-col gap-4">
                        @foreach($dataHasil['similar_games_reference'] as $index => $game)
                            @php
                                $borderClass = 'border-gray-700';
                                $badgeClass = 'bg-gray-700 text-gray-300';
                                $rankText = '#' . ($index + 1);

                                if($index == 0) {
                                    $borderClass = 'border-yellow-500'; 
                                    $badgeClass = 'bg-yellow-500 text-gray-900 font-extrabold';
                                } elseif($index == 1) {
                                    $borderClass = 'border-gray-300'; 
                                    $badgeClass = 'bg-gray-300 text-gray-900 font-bold';
                                } elseif($index == 2) {
                                    $borderClass = 'border-orange-700'; 
                                    $badgeClass = 'bg-orange-700 text-white font-bold';
                                }
                            @endphp

                            <div class="bg-gray-800 rounded-lg p-5 flex items-center justify-between border-l-4 {{ $borderClass }} hover:bg-gray-750 transition">
                                <div class="flex items-center gap-5">
                                    <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center text-sm {{ $badgeClass }}">
                                        {{ $rankText }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold {{ $index == 0 ? 'text-yellow-400' : 'text-white' }}">{{ $game['title'] }}</h3>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <p><span class="font-semibold text-gray-500">Genre:</span> {{ $game['genres'] }}</p>
                                            <p><span class="font-semibold text-gray-500">Jarak Spasial (Distance):</span> <span class="font-mono text-purple-300">{{ $game['distance'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block text-xs text-gray-500 mb-1">Rating Asli</span>
                                    <span class="text-md font-mono bg-gray-900 px-3 py-1 rounded border border-gray-600 text-yellow-400 font-bold">{{ $game['score'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @endif

    </div>

    @if(session('error_modal'))
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-gray-800 border-2 border-red-500 rounded-2xl p-8 max-w-md w-full shadow-2xl text-center transform transition-transform scale-100">
            
            <div class="flex justify-center mb-4">
                <div class="bg-red-500/20 p-4 rounded-full">
                    <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-white mb-2">Oops! Input Ditolak</h3>
            <p class="text-gray-300 mb-8 leading-relaxed">
                {{ session('error_modal') }}
            </p>

            <button onclick="document.getElementById('errorModal').style.display='none'" class="bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-8 rounded-full transition-colors w-full shadow-lg">
                Paham & Perbaiki
            </button>
        </div>
    </div>
    @endif

</body>
</html>