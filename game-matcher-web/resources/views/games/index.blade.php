<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Matcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen">

    <nav class="bg-gray-800 p-4 shadow-lg border-b border-gray-700">
        <div class="container mx-auto text-center">
            <a href="/" class="text-2xl font-bold text-blue-500 tracking-wider">GAME<span class="text-white">MATCHER</span></a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-10 max-w-6xl">
        
        <div class="bg-gray-800 p-8 rounded-xl shadow-2xl mb-12 border border-gray-700">
            <h1 class="text-3xl font-bold mb-2 text-center">CHOOSE YOUR GAME</h1>
            <p class="text-gray-400 mb-8 text-center text-sm">Pilih 1 hingga 3 game.</p>
            
            <form action="/" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @for ($i = 1; $i <= 3; $i++)
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Game {{ $i }} {{ $i == 1 ? '(Wajib)' : '(Opsional)' }}</label>
                        
                        <div class="relative custom-select-wrapper">
                            <input type="hidden" name="games[]" class="real-input" {{ $i == 1 ? 'required' : '' }}>
                            
                            <button type="button" class="select-btn w-full bg-gray-900 border border-gray-600 text-white rounded p-3 pr-12 text-left focus:outline-none focus:border-blue-500 cursor-pointer flex justify-between items-center transition">
                                <span class="selected-text text-gray-400 truncate">-- Pilih Game --</span>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            
                            <ul class="options-list absolute z-50 w-full bg-gray-800 border border-gray-600 mt-1 rounded-lg shadow-2xl hidden max-h-64 overflow-y-auto">
                                <li data-value="" class="p-3 text-gray-400 hover:bg-gray-700 cursor-pointer transition border-b border-gray-700">-- Pilih Game --</li>
                                @foreach($allGames as $g)
                                    <li data-value="{{ $g['id'] }}" class="p-3 text-white hover:bg-blue-600 cursor-pointer transition border-b border-gray-700 last:border-0">{{ $g['title'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endfor
                </div>
                
                <div class="text-center mt-6 flex justify-center gap-4">
                    <a href="/" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-full transition shadow-lg">Reset</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-full transition shadow-lg">Cari Rekomendasi</button>
                </div>
            </form>
        </div>

        @if(!empty($selectedInfo) && count($recommendations) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <h3 class="text-xl font-bold mb-4 text-blue-400 border-b border-gray-700 pb-2">Referensi</h3>
                    <div class="space-y-4">
                        @foreach($selectedInfo as $info)
                            <div class="bg-gray-800 p-5 rounded-lg border border-gray-600 shadow-md">
                                <h4 class="text-lg font-bold text-white mb-3">{{ $info['title'] }}</h4>
                                <div class="text-sm space-y-2">
                                    <div class="flex justify-between border-b border-gray-700 pb-1">
                                        <span class="text-gray-400">Genre</span>
                                        <span class="text-gray-200 font-medium text-right ml-4">{{ $info['genres'] }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-700 pb-1">
                                        <span class="text-gray-400">Plays</span>
                                        <span class="text-gray-200 font-medium">{{ number_format($info['plays'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between pt-1">
                                        <span class="text-gray-400">Score</span>
                                        <span class="text-yellow-400 font-bold">{{ $info['score'] }} <span class="text-gray-500 text-xs font-normal">/ 5.0</span></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-xl font-bold mb-4 text-purple-400 border-b border-gray-700 pb-2">Top 5 Rekomendasi</h3>
                    <div class="flex flex-col gap-4">
                        @foreach($recommendations as $index => $game)
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

                            <div class="bg-gray-800 rounded-lg p-6 flex items-center justify-between border-l-4 {{ $borderClass }} hover:bg-gray-750 transition">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 flex-shrink-0 rounded-full flex items-center justify-center text-xl {{ $badgeClass }}">
                                        {{ $rankText }}
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold {{ $index == 0 ? 'text-yellow-400' : 'text-white' }}">{{ $game['title'] }}</h3>
                                        <div class="text-xs text-gray-400 mt-1 space-y-1">
                                            <p><span class="font-semibold text-gray-500">Genre:</span> {{ $game['genres'] }}</p>
                                            <p><span class="font-semibold text-gray-500">Plays:</span> {{ number_format($game['plays'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block text-sm text-gray-500 mb-1">Rating Game</span>
                                    <span class="text-lg font-mono bg-gray-900 px-3 py-1 rounded border border-gray-600">{{ $game['score'] }} / 5.0</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrappers = document.querySelectorAll('.custom-select-wrapper');
            
            wrappers.forEach(wrapper => {
                const btn = wrapper.querySelector('.select-btn');
                const list = wrapper.querySelector('.options-list');
                const input = wrapper.querySelector('.real-input');
                const selectedText = wrapper.querySelector('.selected-text');
                const options = wrapper.querySelectorAll('.options-list li');

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll('.options-list').forEach(el => {
                        if(el !== list) el.classList.add('hidden');
                    });
                    list.classList.toggle('hidden');
                });

                options.forEach(option => {
                    option.addEventListener('click', () => {
                        const value = option.getAttribute('data-value');
                        const text = option.innerText;
                        
                        input.value = value; 
                        
                        selectedText.innerText = text; 
                        selectedText.classList.remove('text-gray-400');
                        selectedText.classList.add('text-white');
                        
                        list.classList.add('hidden'); 
                    });
                });
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.custom-select-wrapper')) {
                    document.querySelectorAll('.options-list').forEach(el => {
                        el.classList.add('hidden');
                    });
                }
            });
        });
    </script>
</body>
</html>