<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    /**
     * Menampilkan halaman utama form simulasi prediksi rating
     */
    public function index(Request $request)
    {
        $dataHasil = null;

        $plays = $request->input('plays');

        if ($plays !== null) {
            $playing = $request->input('playing', '0');
            $backlogs = $request->input('backlogs', '0');
            $wishlist = $request->input('wishlist', '0');
            
            $genresArray = $request->input('genres', []);
            $genres = implode(',', $genresArray);

            try {
                $res = Http::timeout(10)->get("http://127.0.0.1:5000/api/predict", [
                    'plays' => $plays,
                    'playing' => $playing,
                    'backlogs' => $backlogs,
                    'wishlist' => $wishlist,
                    'genres' => $genres
                ]);

                if ($res->successful()) {
                    $dataHasil = $res->json();
                } else {
                    $errorData = $res->json();
                    $pesanError = $errorData['message'] ?? 'Terjadi kesalahan pada input data.';
                    
                    session()->now('error_modal', $pesanError);
                }
            } catch (\Exception $e) {
                session()->now('error_modal', 'Server AI Python (Flask) belum dijalankan atau bermasalah.');
            }
        }

        return view('games.index', compact('dataHasil'));
    }
}