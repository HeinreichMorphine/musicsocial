import unittest
from unittest.mock import patch, MagicMock
import pandas as pd
import json
import sys
import os

# Add current directory to path so we can import app
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import app as app_module # Import the module to access globals

class TestRecommender(unittest.TestCase):
    def setUp(self):
        self.app = app_module.app.test_client()
        self.app.testing = True
        
        # Manually set the global engine to a mock to bypass the None check
        self.mock_engine = MagicMock()
        app_module.engine = self.mock_engine
        
        # Manually set algo to a mock
        self.mock_algo = MagicMock()
        app_module.algo = self.mock_algo

    @patch('app.pd.read_sql')
    def test_cold_start_content_based(self, mock_read_sql):
        # Mock DB Connection Context Manager
        mock_conn = MagicMock()
        self.mock_engine.connect.return_value.__enter__.return_value = mock_conn

        # Mock Data
        # 1. all_songs_df
        all_songs_data = {
            'id': [1, 2, 3],
            'artist_name': ['Artist A', 'Artist B', 'Artist C'],
            'genres': [json.dumps(['pop']), json.dumps(['rock']), json.dumps(['jazz'])]
        }
        all_songs_df = pd.DataFrame(all_songs_data)

        # 2. user_interacted_song_ids (Empty for Cold Start)
        user_interactions_df = pd.DataFrame({'song_id': []})

        # 3. liked_artists (User likes Artist A)
        liked_artists_df = pd.DataFrame({'artist_name': ['Artist A']})

        # 4. liked_genres (User likes Pop)
        liked_genres_df = pd.DataFrame({'genre_item': [json.dumps(['pop'])]})
        
        # 5. social_graph (Empty) - This is returned by connection.execute
        social_graph_result = [] 

        # 6. song_sharers (Empty)
        sharers_df = pd.DataFrame({'song_id': [], 'user_id': []})

        mock_read_sql.side_effect = [
            all_songs_df,
            user_interactions_df,
            liked_artists_df,
            liked_genres_df,
            sharers_df
        ]

        # Mock connection.execute for social graph
        # The query returns a list of tuples (user_id,)
        mock_conn.execute.return_value = social_graph_result

        # Call endpoint
        response = self.app.get('/recommendations/1')
        data = response.get_json()

        self.assertEqual(response.status_code, 200, msg=f"Response failed with: {data.get('message')}")
        self.assertIn('recommendations', data)
        recs = data['recommendations']
        
        # Expect Song 1 (Artist A, Pop) to be recommended due to content match
        rec_ids = [r['song_id'] for r in recs]
        self.assertIn(1, rec_ids)
        
        # Check reason
        rec_1 = next(r for r in recs if r['song_id'] == 1)
        # Reason might be "Same artist: Artist A & Similar genres: pop"
        self.assertTrue("Same artist" in rec_1['reason'] or "Similar genres" in rec_1['reason'])

    @patch('app.pd.read_sql')
    def test_social_boost(self, mock_read_sql):
        # Mock DB Connection
        mock_conn = MagicMock()
        self.mock_engine.connect.return_value.__enter__.return_value = mock_conn

        # Mock Data
        # 1. all_songs_df
        all_songs_data = {
            'id': [1, 2],
            'artist_name': ['Artist A', 'Artist B'],
            'genres': [json.dumps(['pop']), json.dumps(['rock'])]
        }
        all_songs_df = pd.DataFrame(all_songs_data)

        # 2. user_interacted_song_ids (Has history)
        # User has interacted with 5 songs (mocking length check)
        user_interactions_df = pd.DataFrame({'song_id': [10, 11, 12, 13, 14]}) 

        # 3. liked_artists
        liked_artists_df = pd.DataFrame({'artist_name': []})

        # 4. liked_genres
        liked_genres_df = pd.DataFrame({'genre_item': []})
        
        # 5. social_graph (User 1 follows User 2)
        social_graph_result = [(2,)] 

        # 6. song_sharers (User 2 shared Song 1)
        sharers_df = pd.DataFrame({'song_id': [1], 'user_id': [2]})

        mock_read_sql.side_effect = [
            all_songs_df,
            user_interactions_df,
            liked_artists_df,
            liked_genres_df,
            sharers_df
        ]

        mock_conn.execute.return_value = social_graph_result

        # Mock SVD prediction
        self.mock_algo.predict.return_value.est = 1.0 # Base score

        # Call endpoint
        response = self.app.get('/recommendations/1')
        data = response.get_json()

        self.assertEqual(response.status_code, 200, msg=f"Response failed with: {data.get('message')}")
        recs = data['recommendations']
        
        # Check Song 1
        rec_1 = next(r for r in recs if r['song_id'] == 1)
        
        # Base score 1.0 * 0.7 = 0.7
        # Social boost 1.5 * 0.3 = 0.45
        # Total ~ 1.15
        self.assertAlmostEqual(rec_1['score'], 1.15, delta=0.1)
        self.assertIn("Liked by your friend", rec_1['reason'])

if __name__ == '__main__':
    unittest.main()
