from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.neighbors import NearestNeighbors
from sklearn.preprocessing import MultiLabelBinarizer, MinMaxScaler
import ast

app = Flask(__name__)

print("Membaca dataset Kaggle dari file lokal...")
df = pd.read_csv('games.csv')

df = df.rename(columns={
    'Unnamed: 0': 'id', 
    'Title': 'title', 
    'Rating': 'score'
})

df = df.dropna(subset=['id', 'title', 'Genres', 'score'])

df = df.drop_duplicates(subset=['title']) 

df = df.reset_index(drop=True)

def parse_genres(x):
    try:
        return ast.literal_eval(x)
    except:
        return []

df['Genres'] = df['Genres'].apply(parse_genres)

mlb = MultiLabelBinarizer()
genre_encoded = mlb.fit_transform(df['Genres'])
genre_df = pd.DataFrame(genre_encoded, columns=mlb.classes_)

def convert_k_to_number(x):
    if isinstance(x, str):
        if 'K' in x:
            return float(x.replace('K', '')) * 1000
        else:
            try:
                return float(x)
            except:
                return 0
    return float(x) if pd.notnull(x) else 0

df['Plays'] = df['Plays'].apply(convert_k_to_number)

df_final = pd.concat([df[['score', 'Plays']], genre_df], axis=1)

scaler = MinMaxScaler()
df_scaled = scaler.fit_transform(df_final)

# melatih model KNN
knn = NearestNeighbors(n_neighbors=6, metric='euclidean')
knn.fit(df_scaled)

@app.route('/api/games', methods=['GET'])
def get_all_games():
    games_list = df[['id', 'title']].to_dict('records')
    return jsonify(games_list)

@app.route('/api/recommend', methods=['GET'])
def recommend():
    game_ids_str = request.args.get('id', type=str)
    if not game_ids_str:
        return jsonify({'error': 'ID tidak boleh kosong'}), 400

    game_ids = [int(x) for x in game_ids_str.split(',') if x.strip().isdigit()]
    game_indices = df[df['id'].isin(game_ids)].index.tolist()

    if not game_indices:
        return jsonify({'error': 'Game ID tidak ditemukan'}), 404

    selected_info = []
    for idx in game_indices:
        row = df.loc[idx]
        selected_info.append({
            'title': str(row['title']),
            'genres': ", ".join(row['Genres']), 
            'plays': int(row['Plays']),
            'score': float(row['score'])
        })

    selected_vectors = df_scaled[game_indices]
    mean_vector = np.mean(selected_vectors, axis=0).reshape(1, -1)

    n_neighbors_to_find = 5 + len(game_indices)
    distances, indices = knn.kneighbors(mean_vector, n_neighbors=n_neighbors_to_find)

    results = []
    for i in range(len(indices[0])):
        idx = indices[0][i]
        if df.iloc[idx]['id'] not in game_ids:
            results.append({
                'game_id': int(df.iloc[idx]['id']),
                'title': str(df.iloc[idx]['title']),
                'score': float(df.iloc[idx]['score']),
                'genres': ", ".join(df.iloc[idx]['Genres']), 
                'plays': int(df.iloc[idx]['Plays'])
            })
        if len(results) == 5:
            break

    return jsonify({
        'recommendations': results,
        'selected_info': selected_info
    })

if __name__ == '__main__':
    app.run(port=5000, debug=True)