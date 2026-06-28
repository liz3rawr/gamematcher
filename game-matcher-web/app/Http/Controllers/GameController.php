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
        // Inisialisasi awal variabel kosong agar Blade tidak error/undefined variable saat pertama dibuka
        $dataHasil = null;

        // Ambil data input dari form simulasi (jika ada request masuk)
        $plays = $request->input('plays');

        // Jika user sudah menekan tombol "Prediksi Rating" (ditandai dengan adanya input 'plays')
        if ($plays !== null) {
            $playing = $request->input('playing', '0');
            $backlogs = $request->input('backlogs', '0');
            $wishlist = $request->input('wishlist', '0');
            
            // Satukan array checkbox genre menjadi string pisahan koma (Contoh: "RPG,Adventure")
            $genresArray = $request->input('genres', []);
            $genres = implode(',', $genresArray);

            try {
                // Tembak endpoint API Flask baru kita (/api/predict)
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
                    // TANGKAP ERROR DARI PYTHON (KODE 400)
                    $errorData = $res->json();
                    $pesanError = $errorData['message'] ?? 'Terjadi kesalahan pada input data.';
                    
                    // GANTI ->flash() MENJADI ->now()
                    session()->now('error_modal', $pesanError);
                }
            } catch (\Exception $e) {
                // GANTI ->flash() MENJADI ->now()
                session()->now('error_modal', 'Server AI Python (Flask) belum dijalankan atau bermasalah.');
            }
        }

        // Return ke view games.index sesuai dengan path folder di struktur Laravel-mu
        return view('games.index', compact('dataHasil'));
    }
}