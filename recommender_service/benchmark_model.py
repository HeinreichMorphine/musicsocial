from app import init_db_connection, fetch_data_from_db
from surprise import SVD, Dataset, Reader
from surprise.model_selection import cross_validate
import os
import pandas as pd

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
        # Using the same rating scale as app.py (-1 to 1.5)
        reader = Reader(rating_scale=(-1, 1.5))
        data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)
        
        # 2. Define Model
        # Using same parameters as trained model for fair evaluation
        algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
        
        # 3. Run 5-Fold Cross Validation
        print("\nStarting 5-Fold Cross Validation...")
        print("This may take a moment depending on dataset size...")
        results = cross_validate(algo, data, measures=['RMSE', 'MAE'], cv=5, verbose=True)
        
        # 4. output
        print("\n--- Benchmark Results ---")
        print(f"Average Root Mean Square Error (RMSE): {results['test_rmse'].mean():.4f}")
        print(f"Average Mean Absolute Error (MAE): {results['test_mae'].mean():.4f}")
        print("-------------------------")
