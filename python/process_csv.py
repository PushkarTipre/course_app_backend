import pandas as pd
import numpy as np
import json
import sys
import os
from xgboost import XGBClassifier
import warnings
warnings.filterwarnings('ignore')

# Check if all required arguments are provided
if len(sys.argv) < 4:
    print(json.dumps({
        "status": "error",
        "message": "Required arguments: <csv_path> <model_dir> <output_dir> [optional_user_id]"
    }))
    sys.exit(1)

# Get command line arguments
csv_path = sys.argv[1]
model_dir = sys.argv[2]
output_dir = sys.argv[3]

# Optional user ID parameter
specific_user_id = None
if len(sys.argv) > 4:
    specific_user_id = sys.argv[4]

def time_to_seconds(time_str):
    """Convert time string to seconds"""
    try:
        if pd.isna(time_str) or time_str == '':
            return 0
        h, m, s = map(int, time_str.split(':'))
        return h * 3600 + m * 60 + s
    except:
        return 0

def analyze_pauses(pause_str):
    """Analyze pause durations"""
    if pd.isna(pause_str) or pause_str == '':
        return 0, 0
    pauses = [time_to_seconds(d) for d in pause_str.split(';')]
    return np.mean(pauses), len(pauses)

def load_model():
    """Load XGBoost model and metadata from JSON files"""
    try:
        # Load model from JSON
        model_path = os.path.join(model_dir, "learning_model.json")
        model = XGBClassifier()
        model.load_model(model_path)
        
        # Load feature columns and label mappings
        with open(os.path.join(model_dir, "feature_columns.json"), "r") as f:
            feature_columns = json.load(f)
        
        with open(os.path.join(model_dir, "label_mapping.json"), "r") as f:
            label_mapping = json.load(f)
            
        return model, feature_columns, label_mapping
    
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Failed to load model: {str(e)}"
        }))
        sys.exit(1)

def preprocess_data(data):
    """Preprocess the input data using the same steps as during training"""
    try:
        # Convert time to seconds
        data['Session_Seconds'] = data['Session Total Watch Time'].apply(time_to_seconds)
        
        # Pause analysis
        data[['Avg_Pause', 'Pause_Count']] = data['Pause Durations'].apply(
            lambda x: pd.Series(analyze_pauses(x)))
        
        # Progress analysis
        data['Final_Progress'] = data['Video Progress'].apply(
            lambda x: float(x.split(';')[-1]) if isinstance(x, str) and x else 0)
        
        # Sort data by User ID and session date/time to identify the latest session
        if 'Session Date' in data.columns and 'Session Time' in data.columns:
            data['Session_DateTime'] = pd.to_datetime(data['Session Date'] + ' ' + data['Session Time'], 
                                                     errors='coerce')
        elif 'Session Date' in data.columns:
            data['Session_DateTime'] = pd.to_datetime(data['Session Date'], errors='coerce')
        else:
            # If no date columns, use row order as proxy for time
            data['Session_DateTime'] = data.index
            
        # Sort by datetime
        data = data.sort_values(['User ID', 'Session_DateTime'])
        
        # Get the last session for each user
        last_sessions = data.drop_duplicates(subset=['User ID'], keep='last')
        
        # Quiz analysis - only use the last session's quiz score for each user
        # Create a mapping of user ID to last quiz score
        last_quiz_scores = dict(zip(last_sessions['User ID'], 
                                   last_sessions['Quiz Score'].apply(
                                       lambda x: sum(map(int, x.split(';'))) if isinstance(x, str) and x else 0)))
        
        # Apply this to the original data
        data['Quiz_Total'] = data['User ID'].map(last_quiz_scores)
        
        return data
    
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Error preprocessing data: {str(e)}"
        }))
        sys.exit(1)
def create_user_features(data):
    """Create features for each user, identical to training process"""
    try:
        
        features = data.groupby('User ID').agg({
            'Session_Seconds': ['mean', 'sum'],
            'Avg_Pause': 'mean',
            'Pause_Count': 'mean',
            'Final_Progress': 'mean',
            'Quiz_Total': 'first',
            'Course ID': 'nunique'
        }).reset_index()
        
        features.columns = [
            'User_ID', 'Avg_Session_Time', 'Total_Time',
            'Avg_Pause_Duration', 'Total_Pauses',
            'Avg_Progress', 'Total_Quiz_Score', 'Courses_Taken'
        ]
        
        # Efficiency metric
        features['Learning_Efficiency'] = (
            (features['Avg_Progress'] * features['Total_Quiz_Score']) / 
            (features['Avg_Session_Time'] + 1)
        )
        
        return features
    
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Error creating user features: {str(e)}"
        }))
        sys.exit(1)

def predict_learning_pace(features, model, feature_columns, label_mapping):
    """Use the pre-trained model to predict learning pace"""
    try:
        # Ensure we have all required features
        for col in feature_columns:
            if col not in features.columns:
                features[col] = 0
        
        # Extract features in correct order
        X = features[feature_columns]
        
        # Make predictions using the pre-trained model
        predictions = model.predict(X)
        
        # Map predictions to human-readable labels
        results = []
        for i, user_id in enumerate(features['User_ID']):
            user_data = features.iloc[i]
            prediction_idx = str(predictions[i])
            pace = label_mapping[prediction_idx]
            
            results.append({
                'user_id': int(user_id),
                'learning_pace': pace,
                'avg_session_time': float(user_data['Avg_Session_Time']),
                'total_time': float(user_data['Total_Time']),
                'avg_progress': float(user_data['Avg_Progress']),
                'total_quiz_score': float(user_data['Total_Quiz_Score']),
                'learning_efficiency': float(user_data['Learning_Efficiency']),
                'courses_taken': int(user_data['Courses_Taken']),
                'total_pauses': float(user_data['Total_Pauses'])
            })
        
        return results
    
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Error making predictions: {str(e)}"
        }))
        sys.exit(1)

def main():
    try:
        # Create original filename without full path for output naming
        filename_base = os.path.splitext(os.path.basename(csv_path))[0]
        
        # Load the CSV file
        data = pd.read_csv(csv_path)
        
        # If a specific user ID was provided, filter the data for just that user
        if specific_user_id is not None:
            # Convert specific_user_id to the appropriate type based on your data
            try:
                specific_user_id_int = int(specific_user_id)
                data = data[data['Unique ID'] == specific_user_id_int]
            except ValueError:
                # If the user ID is not an integer, try as string
                data = data[data['Unique ID'] == specific_user_id]
                
            if len(data) == 0:
                print(json.dumps({
                    "status": "error",
                    "message": f"User ID {specific_user_id} not found in the CSV data"
                }))
                sys.exit(1)
        
        # Load pre-trained model and metadata
        model, feature_columns, label_mapping = load_model()
        
        # Preprocess the data (no training involved)
        processed_data = preprocess_data(data)
        
        # Create user features  
        user_features = create_user_features(processed_data)
        
        # Make predictions using the pre-trained model
        predictions = predict_learning_pace(user_features, model, feature_columns, label_mapping)
        
        # Create a more detailed output including overall statistics
        output_data = {
            "predictions": predictions,
            "summary": {
                "total_users": len(predictions),
                "pace_distribution": {
                    "slow": len([p for p in predictions if p["learning_pace"] == "slow"]),
                    "average": len([p for p in predictions if p["learning_pace"] == "average"]),
                    "fast": len([p for p in predictions if p["learning_pace"] == "fast"])
                }
            }
        }
        
        # Add user ID to output filename if specific one was provided
        output_suffix = f"_user{specific_user_id}" if specific_user_id else ""
        
        # Save results to output directory
        output_file = os.path.join(output_dir, f"results_{filename_base}{output_suffix}.json")
        with open(output_file, 'w') as f:
            json.dump(output_data, f)
            
        # Save CSV results
        csv_results = []
        for p in predictions:
            csv_results.append({
                'user_id': p['user_id'],
                'learning_pace': p['learning_pace'],
                'avg_session_time': p['avg_session_time'],
                'total_time': p['total_time'],
                'avg_progress': p['avg_progress'],
                'total_quiz_score': p['total_quiz_score'],
                'learning_efficiency': p['learning_efficiency']
            })
            
        # Create CSV result file
        csv_output_file = os.path.join(output_dir, f"{filename_base}{output_suffix}_result.csv")
        pd.DataFrame(csv_results).to_csv(csv_output_file, index=False)
        
        # Print JSON results for PHP to capture
        print(json.dumps({
            "status": "success",
            "message": "Analysis completed successfully",
            "predictions_count": len(predictions),
            "output_file": output_file,
            "csv_output_file": csv_output_file
        }))
        
        print(f"Results saved to: {output_file}")
        print(f"CSV Results saved to: {csv_output_file}")
        
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": f"Error in analysis: {str(e)}"
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()