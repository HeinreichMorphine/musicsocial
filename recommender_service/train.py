from app import init_db_connection, train_and_save_model
import os

if __name__ == "__main__":
    # Ensure we are in the right directory for relative paths (like .env)
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    
    print("Starting training script...")
    init_db_connection()
    train_and_save_model()
    print("Training script finished.")
