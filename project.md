✦ To test the recommendation system algorithm, you need to first start the Python Flask application and
  then make an HTTP GET request to its recommendations endpoint.

  1. Start the Python Flask Application:

  Open your terminal, navigate to the recommender_service directory, and run the app.py file:

   1 cd C:\laragon\www\musicsocial-main\recommender_service
   2 python app.py

  This will start the Flask application, typically on http://0.0.0.0:5000 (or http://127.0.0.1:5000).
  You should see output in your terminal indicating that the service is running and the model is being
  loaded or trained.

  2. Make an HTTP GET Request to the Recommendations Endpoint:

  Once the Flask app is running, you can test it by making a GET request to the
  /recommendations/<user_id> endpoint. Replace <user_id> with an actual user ID from your database
  (e.g., 1).

  You can do this using curl in your terminal:

   1 curl http://127.0.0.1:5000/recommendations/1

  Or, you can simply open your web browser and navigate to:

   1 http://127.0.0.1:5000/recommendations/1

  The service should return a JSON response containing recommendations for the specified user ID. If the
   model hasn't been trained yet or there's no data, it might return an error message indicating that.
