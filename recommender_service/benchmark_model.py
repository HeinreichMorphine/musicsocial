from app import init_db_connection, fetch_data_from_db
from surprise import SVD, Dataset, Reader
from surprise.model_selection import train_test_split
import os
import pandas as pd
import numpy as np
import math
from collections import defaultdict

def get_top_n(predictions, n=10):
    """Return the top-N recommendation for each user from a set of predictions.

    Args:
        predictions(list of Prediction objects): The list of predictions, as
            returned by the test method of an algorithm.
        n(int): The number of recommendation to output for each user. Default
            is 10.

    Returns:
    A dict where keys are user (raw) ids and values are lists of tuples:
        [(raw item id, rating estimation), ...] of size n.
    """
    # First map the predictions to each user.
    top_n = defaultdict(list)
    for uid, iid, true_r, est, _ in predictions:
        top_n[uid].append((iid, est))

    # Then sort the predictions for each user and retrieve the k highest ones.
    for uid, user_ratings in top_n.items():
        user_ratings.sort(key=lambda x: x[1], reverse=True)
        top_n[uid] = user_ratings[:n]

    return top_n

def precision_recall_at_k(predictions, k=10, threshold=0.1):
    """Return precision and recall at k metrics for each user."""

    # First map the predictions to each user.
    user_est_true = defaultdict(list)
    for uid, iid, true_r, est, _ in predictions:
        user_est_true[uid].append((est, true_r))

    precisions = dict()
    recalls = dict()

    for uid, user_ratings in user_est_true.items():

        # Sort user ratings by estimated value
        user_ratings.sort(key=lambda x: x[0], reverse=True)

        # Number of relevant items
        n_rel = sum((true_r >= threshold) for (_, true_r) in user_ratings)

        # Number of recommended items in top k
        n_rec_k = sum((est >= threshold) for (est, _) in user_ratings[:k])

        # Number of relevant and recommended items in top k
        n_rel_and_rec_k = sum(((true_r >= threshold) and (est >= threshold))
                              for (est, true_r) in user_ratings[:k])

        # Precision@K: Proportion of recommended items that are relevant
        precisions[uid] = n_rel_and_rec_k / k if k != 0 else 0

        # Recall@K: Proportion of relevant items that are recommended
        recalls[uid] = n_rel_and_rec_k / n_rel if n_rel != 0 else 0

    return precisions, recalls

def ndcg_at_k(predictions, k=10, threshold=0.1):
    """Return NDCG@k for each user."""
    user_est_true = defaultdict(list)
    for uid, iid, true_r, est, _ in predictions:
        user_est_true[uid].append((est, true_r))
        
    ndcg_scores = dict()
    
    for uid, user_ratings in user_est_true.items():
        # Sort by predicted score (our ranking)
        user_ratings.sort(key=lambda x: x[0], reverse=True)
        
        # Calculate DCG
        dcg = 0
        for i in range(min(k, len(user_ratings))):
            est, true_r = user_ratings[i]
            if true_r >= threshold:
                rel = 1
            else:
                rel = 0
            dcg += (2**rel - 1) / math.log2(i + 2)
            
        # Calculate IDCG (Ideal DCG)
        # Sort by true rating (ideal ranking)
        ideal_ratings = sorted(user_ratings, key=lambda x: x[1], reverse=True)
        idcg = 0
        for i in range(min(k, len(ideal_ratings))):
            _, true_r = ideal_ratings[i]
            if true_r >= threshold:
                rel = 1
            else:
                rel = 0
            idcg += (2**rel - 1) / math.log2(i + 2)
            
        if idcg == 0:
            ndcg_scores[uid] = 0
        else:
            ndcg_scores[uid] = dcg / idcg
            
    return ndcg_scores

if __name__ == "__main__":
    # Ensure consistency in CWD
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    
    print("Initializing Database Connection...")
    init_db_connection()
    
    print("Fetching Interaction Data...")
    interactions_df = fetch_data_from_db()
    
    if interactions_df.empty:
        print("Error: No interaction data found in database. Cannot benchmark model.")
    else:
        print(f"Data loaded: {len(interactions_df)} rows.")
        
        # 1. Load Data
        reader = Reader(rating_scale=(0, 6))
        data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)
        
        # 2. Train/Test Split (80/20) - Logic from Rimal et al. (2025)
        # "Ensures majority of data (80%) is available for learning... while 20% serves as objective benchmark"
        trainset, testset = train_test_split(data, test_size=0.2, random_state=42)
        
        # 3. Define Model
        algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
        
        # 4. Train on Trainset
        print("Training model on 80% split...")
        algo.fit(trainset)
        
        # 5. Predict on Testset
        print("Predicting on Testset...")
        predictions = algo.test(testset)

        # OPTIONAL: K-Fold Cross Valuation (Robustness check)
        from surprise.model_selection import cross_validate
        print("\n--- Running 5-Fold Cross Validation (Robustness Check) ---")
        cv_results = cross_validate(algo, data, measures=['RMSE', 'MAE'], cv=5, verbose=True)

        
        # 6. Calculate Metrics (RMSE/MAE)
        from surprise import accuracy
        print("\n--- Standard Metrics ---")
        rmse = accuracy.rmse(predictions, verbose=False)
        mae = accuracy.mae(predictions, verbose=False)
        print(f"RMSE: {rmse:.4f}")
        print(f"MAE:  {mae:.4f}")
        
        # 7. Calculate Ranking Metrics (Precision@K, NDCG@K)
        # We use k=12 as established in the app settings
        K = 12
        THRESHOLD = 0.5 # Interaction > 0.5 considered "relevant" (e.g. Likes=1.0)
        
        print(f"\n--- Ranking Metrics (@{K}) ---")
        print(f"Threshold for relevance: {THRESHOLD}")
        
        precisions, recalls = precision_recall_at_k(predictions, k=K, threshold=THRESHOLD)
        ndcgs = ndcg_at_k(predictions, k=K, threshold=THRESHOLD)
        
        avg_precision = sum(prec for prec in precisions.values()) / len(precisions)
        avg_recall = sum(rec for rec in recalls.values()) / len(recalls)
        avg_ndcg = sum(score for score in ndcgs.values()) / len(ndcgs)
        
        print(f"Precision@{K}: {avg_precision:.4f}")
        print(f"Recall@{K}:    {avg_recall:.4f}")
        print(f"NDCG@{K}:      {avg_ndcg:.4f}")
        
        print("\nInterpretation:")
        print(f"- Precision@{K}: On average, {avg_precision*100:.1f}% of top-{K} recommendations were relevant.")
        print(f"- NDCG@{K}:      Ranking quality is {avg_ndcg:.2f}/1.0. (Higher is better)")
