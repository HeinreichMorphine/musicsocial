# 3.6. Testing and Verification

This document outlines the testing strategies used to verify the reliability of the system features, specifically focusing on the **Recommendation Engine**.

## 3.6.1. Functionality Testing


### Manual System Functionality Test Cases

| Test Case | Module | Test Steps | Expected Result | Pass/Fail | Comments |
| :--- | :--- | :--- | :--- | :--- | :--- |
| TC-01 | Authentication (User Registration) | 1. Navigate to the Register page.<br>2. Enter a valid username, email address, and password (minimum 8 characters, with uppercase, lowercase, numbers, and symbols).<br>3. Confirm password.<br>4. Optionally upload a profile picture.<br>5. Click "Register". | System validates all input fields, creates a new user record, sends a verification email, and redirects the user to the email verification notice page. | Pass | Handled by `RegisteredUserController`. Validates password requirements and profile picture size (<2MB) and triggers the registration events. |
| TC-02 | Authentication (Social Registration) | 1. Navigate to the Register page.<br>2. Click "Sign up with Spotify" or "Sign up with Google".<br>3. Complete the OAuth authorization on the provider's page. | System creates a new user account using the provider's profile data (name, email, avatar) and redirects the user to the Dashboard. | Pass | Handled by `SocialAuthController` via Laravel Socialite stateless OAuth callbacks. |
| TC-03 | Authentication (User Login) | 1. Navigate to the Login page.<br>2. Enter registered email and password.<br>3. Optionally check "Remember me".<br>4. Click "Sign in". | System validates credentials, creates a session, and redirects to the Dashboard. A "Forgot password?" link is available for password recovery. | Pass | Handled by `AuthenticatedSessionController` with standard Laravel authentication session lifecycle. |
| TC-04 | Authentication (Social Login) | 1. Navigate to the Login page.<br>2. Click "Continue with Spotify" or "Continue with Google".<br>3. Authorize on the provider's page. | System validates the OAuth token, matches the account to an existing user, creates a session, and redirects to the Dashboard. | Pass | Handled by `SocialAuthController` identifying the existing record via provider ID or matching email address. |
| TC-05 | Authentication (Email Verification) | 1. Register a new account.<br>2. System redirects to the verification notice page and sends a verification email.<br>3. Open the email and click the verification link. | System verifies the email, marks the account as verified, and redirects the user to the Dashboard. A "Resend Verification Email" button is available if the email was not received. | Pass | Handled by `VerifyEmailController` and `EmailVerificationPromptController` with secure signature-verified URLs. |
| TC-04 (Recovery) | Authentication (Forgot Password / Recovery) | 1. Navigate to the Login page.<br>2. Click the "Forgot password?" link.<br>3. Enter registered email.<br>4. Click "Email Password Reset Link".<br>5. Open the received email and click the password reset link.<br>6. Enter and confirm the new password, then click "Reset Password". | System validates the email address, sends a password reset link, and redirects the user. Upon clicking the link, the user is prompted to set a new password, which is successfully updated in the database, allowing them to log in with the new credentials. | Pass | Handled by `PasswordResetLinkController` and `NewPasswordController`. |
| TC-06 | Onboarding (Song Shelf Curation) | 1. Log in with a new account for the first time.<br>2. System redirects to the "Curate Your Song Shelf" page.<br>3. Search for songs and select 5 tracks.<br>4. Click "Complete Onboarding". | System saves the 5 selected songs to the user's shelf and redirects to the Dashboard. | Pass | Handled by `OnboardingController` and enforced via `CheckOnboarding` middleware. Enforces a 5-10 track selection saved in `user_shelf_songs`. |
| TC-07 | Music Integration (Search & Share) | 1. Click "Share Song" on the Dashboard.<br>2. Type a search query (e.g., "Taylor Swift").<br>3. Select a track from the search results and add a caption.<br>4. Click "Post". | System retrieves the song metadata, creates a share post visible on the user's feed and followers' feeds. | Pass | Handled by `SpotifySearchController` and `ShareController`. Saves details in `songs` and `shares` tables. |
| TC-08 | Social Graph (Follow User) | 1. Visit another user's profile page.<br>2. Click the "Follow" button.<br>3. Observe the page state. | Button changes to "Following". The followed user's "Followers" count increments by 1. | Pass | Handled by `FollowController`. Database relations updated in `follows` table correctly. |
| TC-09 | Interaction (Like Post) | 1. Locate a song post on the Dashboard feed.<br>2. Click the "Like" icon. | Like count increments immediately. Icon changes to active color. | Pass | Handled by `LikeController` via AJAX requests, providing instant UI feedback. |
| TC-10 | Interaction (Dislike Post) | 1. Locate a song post on the Dashboard feed.<br>2. Click the "Dislike" icon. | Dislike is registered. If the user previously liked the post, the like is removed first. The dislike is stored as a negative signal for the recommendation engine. | Pass | Handled by `ShareController@toggleDislike`. Mutually exclusive with likes; logged to `song_interactions`. |
| TC-11 | Interaction (Comment on Post) | 1. Locate a song post on the Dashboard feed.<br>2. Type a comment in the comment box.<br>3. Submit. | Comment appears below the post with the user's name and timestamp. Comment count increments. | Pass | Handled by `CommentController`. Supports @mentions and upvoting comments. |
| TC-12 | Discovery (Load Recommendations) | 1. Click "Discovery" in the navigation bar.<br>2. Wait for the page to load. | "Suggested For You" section populates with up to 12 personalized song recommendations based on the user's history, shelf, and social connections. Each song card displays a recommendation reason. | Pass | Handled by `DiscoveryController` calling the Python recommendation API backend `/recommendations/{id}`. |
| TC-13 | Discovery (Fallback State) | 1. Create a new user with no interaction history (only onboarding shelf).<br>2. Visit the Discovery page. | System displays personalized content-based recommendations derived from the user's shelf songs, not generic trending content. | Pass | Handled by the Python service falling back to TF-IDF content filtering based on onboarding shelf data. |
| TC-14 | Playlist (Create, Invite & Collaborate) | 1. Navigate to the Playlists page.<br>2. Create a new playlist with a name and description.<br>3. Open the playlist and invite a friend.<br>4. The invited user accepts the invitation.<br>5. The invited user adds a song to the playlist. | Playlist is created with the user as owner. Invitation is sent and appears as a pending invite for the recipient. Upon acceptance, both users can view and add songs. The added song is visible to all collaborators. | Pass | Handled by `PlaylistController`. Real-time invite status tracked in database. |
| TC-15 | Profile (Update Picture) | 1. Go to the Settings page.<br>2. Upload a JPG image (max 2MB).<br>3. Save changes. | Page refreshes. New avatar is visible in the navigation bar and profile header across all pages. | Pass | Handled by `ProfileController@updatePicture`. Saves file to public disk and links to User avatar. |
| TC-16.1 | Admin Auth & Profile | 1. Navigate to /login/admin.<br>2. Enter valid admin credentials.<br>3. Go to Profile, update name, and change password.<br>4. Click "Logout". | System authenticates the admin. Profile updates are saved. Logout successfully terminates the session and redirects to the admin login page. | Pass | Handled by `AdminController`. Uses separate `admin` guard, allowing secure session division. |
| TC-16.2 | Admin Dashboard | 1. Log in as Admin.<br>2. View the Dashboard page. | Dashboard displays correct aggregate metrics (User count, Share count, etc.), recent activity charts, top 5 genres, and recent user signups. | Pass | Handled by `AdminController@dashboard`. Aggregate queries run efficiently. |
| TC-16.3 | User Management (Ban & Delete) | 1. Navigate to Admin -> Users.<br>2. Search for a specific user.<br>3. Click "Ban".<br>4. Unban the user.<br>5. Click "Delete User". | Ban toggles the user's is_banned status (preventing their login). Delete permanently removes the user and their associated data from the database. | Pass | Handled by `AdminController@banUser` / `deleteUser`. Active sessions deleted on ban. |
| TC-16.4 | Content Moderation | 1. Navigate to Admin -> Moderation.<br>2. Locate a reported/inappropriate Share, Comment, or Playlist.<br>3. Click "Delete" on each entity type. | The selected Share, Comment, or Playlist is permanently deleted from the database and is removed from all public feeds and user profiles. | Pass | Handled by `AdminController` moderation delete endpoints. Clean CASCADE deletions implemented. |
| TC-16.5 | Song Catalog Management | 1. Navigate to Admin -> Songs.<br>2. Click "Add New Song" and fill details.<br>3. Click "Edit" on an existing song and update its metadata.<br>4. Click "Refetch Genres" for a song.<br>5. Click "Delete". | Song is successfully created, updated, and deleted. Refetching genres contacts the Spotify API and updates the song's genre tags in the database. | Pass | Handled by `AdminController` song CRUD methods. Spotify service caches and fetches metadata accurately. |
| TC-16.6 | Recommendation Engine (Retrain & Test) | 1. Navigate to Admin -> Retrain Engine.<br>2. Trigger a manual algorithm retrain.<br>3. Navigate to Admin -> Algo Test Suite.<br>4. Run test scenarios. | System successfully processes the Python retraining script without failing. The Algo Test Suite successfully simulates recommendation requests and returns song arrays. | Pass | Handled by `AdminController@retrainProcess` which communicates with Flask API. Suite renders correctly. |
| TC-16.7 | Admin Management | 1. Navigate to Admin -> Admins.<br>2. Add a new admin by entering a name, email, and password.<br>3. Delete the newly created admin. | The new admin account is created and can log into the panel. Deleting the admin revokes their access immediately. | Pass | Handled by `AdminController` admin creation and deletion logic. |
| TC-17 | Settings (Delete Account) | 1. Navigate to the Settings page and scroll to "Delete Account".<br>2. Enter current password for confirmation.<br>3. Click "Delete Account". | User is logged out. Account is permanently deleted from the database. User can no longer log in with the same credentials. | Pass | Handled by `ProfileController@destroy`. Safely wipes user data and credentials. |
| TC-18 | System Notifications | 1. Log in as User A.<br>2. Have User B follow User A, or comment on User A's share.<br>3. User A clicks the notification bell icon.<br>4. User A clicks "Mark all as read" or clicks on a specific notification. | System correctly generates a Notification entity for the event. The UI displays an unread badge counter. Clicking the notification routes the user to the correct view (profile or post) and updates the unread status in the database. | Pass | Handled by notification controllers. Updates database state and dynamic UI badges in navbar. |

---

## 3.6.2. Accuracy Testing

**Metric**: Root Mean Squared Error (RMSE) & Mean Absolute Error (MAE)
**Library**: `scikit-surprise`

To ensure the SVD algorithm accurately predicts user preferences, we use **5-Fold Cross-Validation**. This splits the interaction dataset into 5 subsets, training on four and testing on one, rotating five times to get a reliable error average.

### Evaluation Metrics:
*   **RMSE (Root Mean Square Error)**: Penalizes large errors heavily. A lower RMSE (typically < 1.0 on a 5-point scale) indicates accurate predictions.
*   **MAE (Mean Absolute Error)**: The average magnitude of errors. Easier to interpret.

### How to Run Evaluation:
The system includes a dedicated benchmarking script that loads live interaction data from the database and performs the cross-validation.

**File**: `recommender_service/benchmark_model.py`
**Execution**: Run `recommender_service/run_benchmark.bat`

```python
# Snippet from recommender_service/benchmark_model.py
if interactions_df.empty:
    print("Error: No interaction data found...")
else:
    # 1. Load Data
    reader = Reader(rating_scale=(0, 6))
    data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)
    
    # 2. Define Model (Same params as app.py)
    algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
    
    # 3. Run 5-Fold Cross Validation
    results = cross_validate(algo, data, measures=['RMSE', 'MAE'], cv=5, verbose=True)
```

---

## 3.6.3. System Integration Testing

**Scope**: End-to-End Flow (Laravel <-> Python)

We verify the complete pipeline using manual or automated integration tests:
1.  **Dashboard Load**: Verify that the "Suggested For You" widget appears within 200ms.
2.  **Fallback Trigger**: If the Python service is offline, ensure Laravel catches the exception and displays a generic fallback (e.g., "Top Songs") or hides the widget without crashing.
3.  **Real-time Updates**: Verify that "Liking" a song immediately updates the `likes` table, which will be included in the next model retraining cycle.
