<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $gamesResponse = Http::get("http://127.0.0.1:5000/api/games");
        $allGames = $gamesResponse->successful() ? $gamesResponse->json() : [];

        $recommendations = [];
        $selectedInfo = []; 

        $selectedGames = array_filter($request->input('games', []), function($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($selectedGames)) {
            $idString = implode(',', $selectedGames);
            
            $res = Http::get("http://127.0.0.1:5000/api/recommend", [
                'id' => $idString
            ]);

            if ($res->successful()) {
                $data = $res->json();
                $recommendations = $data['recommendations'] ?? [];
                $selectedInfo = $data['selected_info'] ?? []; 
            }
        }

        return view('games.index', compact('allGames', 'recommendations', 'selectedInfo'));
    }
}