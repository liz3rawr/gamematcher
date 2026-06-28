from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.neighbors import KNeighborsRegressor

app = Flask(__name__)

# 1. MEMBACA DAN MEMBERSIHKAN DATASET 
print("Membaca dataset...")
df = pd.read_csv('games.csv')

df = df.rename(columns={
    'Unnamed: 0': 'id', 
    'Title': 'title', 
    'Rating': 'score'
})

df = df.dropna(subset=['id', 'title', 'Genres', 'score', 'Plays', 'Playing', 'Backlogs', 'Wishlist'])
df = df.drop_duplicates(subset=['title']) 
df = df.reset_index(drop=True)

# 2. FUNGSI KONVERSI HURUF 'K' (RIBUAN) MENJADI ANGKA
def bersihkan_angka_k(nilai):
    if isinstance(nilai, str):
        if 'K' in nilai:
            return float(nilai.replace('K', '')) * 1000
        try:
            return float(nilai)
        except:
            return 0.0
    return float(nilai) if pd.notnull(nilai) else 0.0

df['Plays'] = df['Plays'].apply(bersihkan_angka_k)
df['Playing'] = df['Playing'].apply(bersihkan_angka_k)
df['Backlogs'] = df['Backlogs'].apply(bersihkan_angka_k)
df['Wishlist'] = df['Wishlist'].apply(bersihkan_angka_k)

# 3. PARSING TEKS GENRE (STRING) MENJADI LIST 
def ubah_teks_genre_ke_list(teks_mentah):
    if not isinstance(teks_mentah, str):
        return []
    
    teks_bersih = teks_mentah.replace("[", "").replace("]", "")
    teks_bersih = teks_bersih.replace("'", "").replace('"', "")
    
    daftar_genre = []
    for g in teks_bersih.split(","):
        nama_genre = g.strip()  
        if nama_genre:          
            daftar_genre.append(nama_genre)
            
    return daftar_genre

df['Genres_List'] = df['Genres'].apply(ubah_teks_genre_ke_list)

# 4. PROSES ENCODE GENRE MANUAL
semua_genre = []
for daftar_genre in df['Genres_List']:
    for g in daftar_genre:
        if g not in semua_genre:
            semua_genre.append(g)
semua_genre = sorted(semua_genre)

# bikin matriks biner (0 dan 1) 
matrix_genre = []
for daftar_genre in df['Genres_List']:
    baris_biner = []
    for g in semua_genre:
        if g in daftar_genre:
            baris_biner.append(1)  # 1 jika game memiliki genre tersebut
        else:
            baris_biner.append(0)  # 0 jika tidak
    matrix_genre.append(baris_biner)

genre_df = pd.DataFrame(matrix_genre, columns=semua_genre)

# 5. NORMALISASI FITUR ANGKA SECARA MANUAL (RUMUS MIN-MAX SCALING)
df_angka = df[['Plays', 'Playing', 'Backlogs', 'Wishlist']].copy()

# Simpan nilai minimal dan maksimal asli dari database
min_asli = df_angka.min()
max_asli = df_angka.max()
print("\n=== BATAS MIN & MAX DATASET ===")
print("NILAI MINIMAL:")
print(min_asli)
print("\nNILAI MAKSIMAL:")
print(max_asli)
print("===============================\n")

# (x - min) / (max - min)
df_angka_scaled = (df_angka - min_asli) / (max_asli - min_asli)

# Gabungkan data angka yang sudah dinormalisasi dengan data genre biner
X = pd.concat([df_angka_scaled, genre_df], axis=1)
y = df['score']

# 6. TRAINING MODEL KNN REGRESSOR (MURNI REGRESI SUPERVISED LEARNING)
knn = KNeighborsRegressor(n_neighbors=5, metric='euclidean')
knn.fit(X.values, y.values)

# 7. ENDPOINT API FLASK UNTUK MENERIMA REQUEST DARI LARAVEL
@app.route('/api/predict', methods=['GET'])
def predict_rating():
    try:
        # Ambil input
        input_plays = bersihkan_angka_k(request.args.get('plays', '0'))
        input_playing = bersihkan_angka_k(request.args.get('playing', '0'))
        input_backlogs = bersihkan_angka_k(request.args.get('backlogs', '0'))
        input_wishlist = bersihkan_angka_k(request.args.get('wishlist', '0'))
        
        input_genres_str = request.args.get('genres', '')
        # ngubah string jadi list
        user_genres = [g.strip() for g in input_genres_str.split(',')] if input_genres_str else []

        # --- VALIDASI MIN/MAX ---
        inputs_dict = {
            'Plays': input_plays, 'Playing': input_playing, 
            'Backlogs': input_backlogs, 'Wishlist': input_wishlist
        }
        
        for kolom, nilai in inputs_dict.items():
            if nilai < min_asli[kolom] or nilai > max_asli[kolom]:
                return jsonify({
                    'status': 'error', 
                    'message': f"Nilai {kolom} tidak valid! Harus di antara {min_asli[kolom]} sampai {max_asli[kolom]}."
                }), 400

        # --- NORMALISASI MANUAL UNTUK DATA BARU ---
        # Data input baru dikurangi min database asli, lalu dibagi selisih max-min database asli
        scaled_plays = (input_plays - min_asli['Plays']) / (max_asli['Plays'] - min_asli['Plays'])
        scaled_playing = (input_playing - min_asli['Playing']) / (max_asli['Playing'] - min_asli['Playing'])
        scaled_backlogs = (input_backlogs - min_asli['Backlogs']) / (max_asli['Backlogs'] - min_asli['Backlogs'])
        scaled_wishlist = (input_wishlist - min_asli['Wishlist']) / (max_asli['Wishlist'] - min_asli['Wishlist'])
        
        vector_angka = [scaled_plays, scaled_playing, scaled_backlogs, scaled_wishlist]

        # --- ENCODE GENRE MANUAL UNTUK DATA BARU ---
        vector_genre = []
        for g in semua_genre:
            if g in user_genres:
                vector_genre.append(1)
            else:
                vector_genre.append(0)

        # Satukan koordinat angka dan genre 
        vektor_uji_lengkap = np.array(vector_angka + vector_genre).reshape(1, -1)

        # Hitung prediksi nilai Rating akhir
        prediksi_rating = knn.predict(vektor_uji_lengkap)[0]

        # Cari 5 game terdekat
        distances, indices = knn.kneighbors(vektor_uji_lengkap, n_neighbors=5)
        
        daftar_game_tetangga = []
        for i in range(5):
            idx = indices[0][i]
            daftar_game_tetangga.append({
                'title': str(df.iloc[idx]['title']),
                'score': float(df.iloc[idx]['score']),
                'genres': ", ".join(df.iloc[idx]['Genres_List']),
                'distance': round(float(distances[0][i]), 4)
            })

        return jsonify({
            'status': 'success',
            'predicted_rating': round(float(prediksi_rating), 2),
            'similar_games_reference': daftar_game_tetangga
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

if __name__ == '__main__':
    app.run(port=5000, debug=True)