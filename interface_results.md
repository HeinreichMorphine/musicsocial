# Reso Web Interface Results Documentation

This chapter documents the web interface pages, modals, and directory structures of the Reso platform. It maps each user interface touchpoint directly to its frontend Blade template files, Laravel routing controllers, and relevant codebase functions.

---

## 4.3 General Access & Authentication Module

### Table 4.3.1 Landing Page Overview
*   **User Interface:** Landing Page: Overview
*   **Blade View File:** [welcome.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php)
*   **Controller Endpoint:** Routing closure inside [web.php:L18-L26](file:///c:/laragon/www/musicsocial-main/routes/web.php#L18-L26)
*   **Description:** Introduces Reso as a social audio platform focused on human-centric music discovery. It redirects authenticated users to the dashboard and guest users to the welcome landing screen.

### Table 4.3.2 Create Account Screen
*   **User Interface:** Account Creation
*   **Blade View File:** [register.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/register.blade.php)
*   **Controller Endpoint:** `App\Http\Controllers\Auth\RegisteredUserController`
*   **Description:** The registration screen allows users to join the community and start sharing their music taste. It offers social authentication quick-links via Spotify and Google, alongside a manual registration form requiring User Name, Email Address, a Profile Picture upload utility, and Password confirmation.
    
    When registering via the Spotify social button, guest users encounter a Closed Beta modal outlining Spotify's API restrictions. If their account is not whitelisted, they are locked from signing up with Spotify and advised to use email or Google; otherwise, whitelisted accounts redirect to Spotify's external credentials page to authorize scopes. Registering via Google prompts the user with Google's account selector to select and authorize their Gmail profile.

### Table 4.3.3 Account Sign-In (Login Screen)
*   **User Interface:** User Login Screen
*   **Blade View File:** [login.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/login.blade.php)
*   **Controller Endpoint:** `App\Http\Controllers\Auth\AuthenticatedSessionController`
*   **Description:** The sign-in screen allows existing users to log back into their Reso accounts. It offers "Continue with Spotify" and "Continue with Google" OAuth methods alongside a manual login form (Email, Password, Remember Me checkbox, and a Forgot Password recovery link). 
    
    If the user clicks "Continue with Spotify", they will encounter a Beta modal detailing API restrictions unless they are whitelisted. On a successful whitelist check, they redirect to Spotify's authentication interface. Selecting Google opens Google's stateless account selector.
    
    #### Spotify & Google Authentication Interface States:
    
    ![Spotify Closed Beta Restricted Login](file:///c:/laragon/www/musicsocial-main/publicimages/spotify_login_restricted.png)
    *Figure 4.3.3a: Spotify Closed Beta Login Restriction Warning Modal*
    
    ![Spotify Sign-In Screen](file:///c:/laragon/www/musicsocial-main/publicimages/spotify_sign_in.png)
    *Figure 4.3.3b: Spotify External Welcome Back Sign-In Redirect Page*

    ![Google Account Chooser Screen](file:///c:/laragon/www/musicsocial-main/publicimages/google_choose_account.png)
    *Figure 4.3.3c: Google External Stateless Account Selection Interface*

### Table 4.3.4 Verify Your Email Page
*   **User Interface:** Email Verification
*   **Blade View File:** [verify-email.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/verify-email.blade.php)
*   **Controller Endpoint:** `App\Http\Controllers\Auth\EmailVerificationPromptController`
*   **Description:** Following registration, users are directed to this verification prompt. It features a simple card layout with an email icon, instructing the user to click the link sent to their inbox to activate their account. A prominent "Resend Email" button is provided for troubleshooting, along with secondary options to "Skip for now" or "Log Out."
    
    #### Email Verification Prompt & Dispatched Activation Email:
    
    ![Email Verification Prompt Screen](file:///c:/laragon/www/musicsocial-main/publicimages/verify_email_prompt.png)
    *Figure 4.3.4a: Email Verification Instruct Card Prompt Page*
    
    ![Dispatched Activation Email Template](file:///c:/laragon/www/musicsocial-main/publicimages/verification_email.png)
    *Figure 4.3.4b: Welcome to Reso Verification Email Notification Layout*

### Table 4.3.5 User Password Recovery & Security Gate Confirmation
*   **User Interface:** Forgot Password, Reset Password, and Confirm Password pages
*   **Blade View Files:** 
    *   [forgot-password.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/forgot-password.blade.php)
    *   [reset-password.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/reset-password.blade.php)
    *   [confirm-password.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/confirm-password.blade.php)
*   **Controller Endpoints:**
    *   `App\Http\Controllers\Auth\PasswordResetLinkController`
    *   `App\Http\Controllers\Auth\NewPasswordController`
    *   `App\Http\Controllers\Auth\ConfirmablePasswordController`
*   **Description:** This combined lifecycle covers credentials updates and security confirmation gates. For **Forgot Password Recovery**, users who have lost credentials enter their registered email address. Submitting initiates the recovery process, sending a validation email containing a secure token. Following the link in the notification email, the user arrives at the **Reset Password Form** containing a secure token. They submit their email and a new password to update their profile in the database. Furthermore, a **Confirm Password Gate** exists for sensitive operations (e.g., unlinking social logins or updating primary credentials), where the application blocks access until users re-input their password, preventing unauthorized requests.
    
    #### Password Security & Recovery Interface Lifecycle States:
    
    ![Forgot Password Recovery Request Screen](file:///c:/laragon/www/musicsocial-main/publicimages/forgot_password.png)
    *Figure 4.3.5a: Request Form for Secure Password Reset Link*
    
    ![Dispatched Password Reset Email Notification](file:///c:/laragon/www/musicsocial-main/publicimages/reset_password_email.png)
    *Figure 4.3.5b: Dispatched System Email containing Reset Password Action Link*
    
    ![Reset Password Update Form Screen](file:///c:/laragon/www/musicsocial-main/publicimages/reset_password.png)
    *Figure 4.3.5c: Form for Assigning and Confirming the New Password*

### Table 4.3.6 Spotify Connection Failure Retry Screen
*   **User Interface:** Spotify OAuth Callback Error Retry
*   **Blade View File:** [spotify-retry.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/auth/spotify-retry.blade.php)
*   **Controller Endpoint:** [SocialAuthController.php:L127-L148](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L127-L148) (`handleSpotifyCallbackError` method)
*   **Description:** Omitted from initial lists. If a Spotify callback handshake fails (e.g. transient state mismatch errors), the system triggers this view. It informs the user and automates up to three silent retries using local scripts before returning a validation error, preventing infinite redirect loops.

---

## 4.4 User Onboarding Module

### Table 4.4.1 Onboarding: Empty Song Shelf Curation
*   **User Interface:** Onboarding Curation (Empty Shelf)
*   **Blade View File:** [genres.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php)
*   **Controller Endpoint:** [OnboardingController.php:L20-L46](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L20-L46) (`genres` method)
*   **Description:** The primary onboarding landing step. It forces users to select a minimum of 5 tracks to prevent recommendation engine cold-start issues. It displays search utilities alongside 5 dashed placeholders representing empty slots. The "Complete Onboarding" submission button is locked by default.

### Table 4.4.2 Onboarding: Curation Selection Process
*   **User Interface:** Onboarding Curation (Populated Shelf)
*   **Blade View File:** [genres.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php) (Populated state logic)
*   **Controller Endpoint:** [OnboardingController.php:L48-L89](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L48-L89) (`store` method)
*   **Description:** Shows search results returned in a grid with selection toggles. As songs are chosen, they populate the shelf. 
*   **Guarantees & Metrics:**
    *   **Onboarding Target:** Set to a minimum of 5 tracks (maximum 10 picks allowed).
    *   **Progress Indicators:** Shows dynamic indicators tracking progress (e.g., `X/5` curation ticks until unlocked, switching to `X/10` once the baseline goal is reached).
    *   **Transitions:** Grid selections and fly-in UI animations execute under `<200ms`.
    *   **Unlocked Actions:** Once $\ge 5$ tracks are chosen, the "Complete Onboarding" button turns purple and becomes clickable.

---

## 4.5 Home Feed & Social Interaction Module

### Table 4.5.1 Home Feed: New User Discovery (Explore Feed)
*   **User Interface:** Home Feed: Explore Curation
*   **Blade View File:** [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) (Explore mode)
*   **Controller Endpoint:** [FeedController.php:L48-L53](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L48-L53) (`index` method with `feed=explore` parameter)
*   **Description:** To prevent new onboarded accounts from landing on empty views, the feed default points to **Explore** (serving a random catalog stream). The "Who to Follow" sidebar displays algorithmic candidate peers (based on user shelf similarity), while "Suggested for you" leverages initial shelf tracks to recommend immediate fallback choices (such as Jazz classics).

### Table 4.5.2 Home Feed: Established User View (Following Feed)
*   **User Interface:** Home Feed: Following Curation
*   **Blade View File:** [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) (Following mode)
*   **Controller Endpoint:** [FeedController.php:L54-L68](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L54-L68)
*   **Description:** Default view for users with followers. Displays a chronological stream of shares from followed users. Users can edit or delete their own posts directly. The sidebar has dynamically generated sections: "Who to Follow" matches users with overlapping Taste DNA vectors, and "Suggested for you" recommends precise items based on SVD predictions (such as "Hype Boy" by NewJeans).

### Table 4.5.3 Home Feed: Song Search & Selection
*   **User Interface:** Home Feed: Real-time Song Search
*   **Blade View File:** [navigation-social.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/navigation-social.blade.php) & [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) (integrated dropdown)
*   **Controller Endpoint:** `App\Http\Controllers\SpotifySearchController@search`
*   **Description:** Typing in the "What are you listening to?" share field fires a debounced, real-time search query. Spotify search results populate a dropdown list containing track details, artists, and high-quality album artwork.

### Table 4.5.4 Home Feed: Recently Played History
*   **User Interface:** Home Feed: Recently Played Curation
*   **Blade View File:** [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) (dropdown tray)
*   **Controller Endpoint:** `App\Http\Controllers\SpotifySearchController@recentlyPlayed`
*   **Description:** Clicking the sharing input for Spotify-linked accounts loads a "RECENTLY PLAYED" dropdown tray containing their latest listening history (from Spotify API player logs), enabling quick shares without manual queries.

### Table 4.5.5 Post Creation & Preview Form
*   **User Interface:** Post Creation & Preview
*   **Blade View File:** Popup layout modal inside [navigation-social.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/navigation-social.blade.php)
*   **Controller Endpoint:** [ShareController.php:L100-L199](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L100-L199) (`store` method)
*   **Description:** Once a track is chosen from search or history, it renders a preview card. Users select if they are "Just Sharing" (standard post) or "Asking for Recommendations" (toggled request) and can add a custom caption.

### Table 4.5.6 Social Interaction: Commenting Page
*   **User Interface:** Social Interaction: Commenting
*   **Blade View File:** [comment.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/comment.blade.php) (included inside dashboard feed/share views)
*   **Controller Endpoint:** `App\Http\Controllers\CommentController@store`
*   **Description:** Comment section situated beneath shared tracks. Displays likes, comments, and bookmark count buttons. Users input textual replies (which can include auto-parsed Spotify links) to post replies.

### Table 4.5.7 Social Interaction: Threaded Replies & Comment CRUD
*   **User Interface:** Social Interaction: Threaded Comments & Actions
*   **Blade View File:** [comment.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/comment.blade.php) (recursive child templates inclusion)
*   **Controller Endpoint:** `App\Http\Controllers\CommentController` (`update`, `destroy`, `toggleUpvote`)
*   **Description:** Supports nested replies. Upvoting comments appends the user's ID to a text upvotes list stored inside the database body. Authors can edit or delete comments, and deleting comments with replies soft-redacts the body to `[deleted]`, cleaning up recursively when child replies reach zero.

### Table 4.5.8 Community Discovery: Seeking Recommendations
*   **User Interface:** Community Discovery: Recommendation Requests
*   **Blade View File:** [show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/shares/show.blade.php) (highlight view state)
*   **Controller Endpoint:** [ShareController.php:L200-L260](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L200-L260) (`show` method)
*   **Description:** When a post is flagged as a recommendations request, it displays a distinct **blue badge** indicating its active state. This activates an integrated Spotify track search widget inside the comment block, enabling friends to directly recommend songs (which are parsed as `[SONG:spotify_id]` tags).

### Table 4.5.9 Single Share Workspace Details Page
*   **User Interface:** Share Details Workspace
*   **Blade View File:** [show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/shares/show.blade.php)
*   **Controller Endpoint:** [ShareController.php:L200-L260](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L200-L260) (`show` method)
*   **Description:** Omitted from initial lists. This page displays a single share post in full focus, including its content card, caption, metadata, and the complete comments list page.

---

## 4.6 Media Integration & Curation Modals

### Table 4.6.1 Media Integration: Spotify Playback
*   **User Interface:** Spotify Playback Embed
*   **Blade View File:** Iframe wrapper inside music card template components
*   **Controller Endpoint:** [SpotifyPlayerController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyPlayerController.php)
*   **Description:** Embeds a Spotify web player. Guest or unlinked users get a 30-second audio preview. Linked users have full access controls linked directly with their Spotify player session.

### Table 4.6.2 Media Integration: YouTube Redirect
*   **User Interface:** YouTube Redirect Action
*   **Blade View File:** Media anchor inside music card components
*   **Controller Endpoint:** Inline helper forwarding user to the YouTube URL
*   **Description:** Fallback playback button. Clicking the YouTube icon forwards the user to the track's linked YouTube video (or a search result query fallback if missing in database), ensuring users without Spotify can listen to the shared track.

### Table 4.6.3 Curation: Native Reso Playlist (Unlinked Menu)
*   **User Interface:** Curation: Native Playlist Addition
*   **Blade View File:** Curation menu dropdown inside music card templates
*   **Description:** For users with unlinked Spotify accounts, clicking the "+" curation icon shows a modal restricted to native Reso playlist additions.

### Table 4.6.4 Curation: Cross-Platform Playlist (Linked Menu)
*   **User Interface:** Curation: Cross-Platform (Spotify / Reso) additions
*   **Blade View File:** Expanded curation modal popup
*   **Description:** For users with a linked Spotify account, the menu expands to offer both native Reso playlist adds and direct exports to their connected Spotify playlists.

### Table 4.6.5 Playlist Selection Modal
*   **User Interface:** Playlist Curation Selector
*   **Blade View File:** Curation add-to-playlist popup modal
*   **Controller Endpoint:** `App\Http\Controllers\PlaylistController@addSong` / `api.playlists.mine` route
*   **Description:** Displays the user's collections. Allows users to select a native playlist or browse their connected Spotify playlists (complete with cover artwork and track counts fetched in real-time) to add the selected song.

---

## 4.7 Collaborative Playlists Module

### Table 4.7.1 Playlists: Collaborative Playlists Home
*   **User Interface:** Playlists landing page
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php)
*   **Controller Endpoint:** [PlaylistController.php:L26-L40](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L26-L40) (`index` method)
*   **Description:** Curates shared playlists. Provides buttons to import a Spotify playlist or create a collaborative playlist from scratch.

### Table 4.7.2 Collaborative Playlist Workspace (Show Page)
*   **User Interface:** Playlist Show Workspace
*   **Blade View File:** [show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/show.blade.php)
*   **Controller Endpoint:** [PlaylistController.php:L83-L119](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L83-L119) (`show` method)
*   **Description:** Omitted from initial lists, this is the main playlist workspace page. It renders the playlist track listing, details, cover art, collaborator list, an embedded Spotify player, and invites widgets.

### Table 4.7.3 Collaborative Playlists: Creation Modal
*   **User Interface:** Collaborative Playlist Creation Form
*   **Blade View File:** Modal form block inside [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php)
*   **Controller Endpoint:** [PlaylistController.php:L58-L81](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L58-L81) (`store` method)
*   **Description:** Creation modal triggered by clicking "New Playlist". It requests a name and description to configure the collaborative asset.

### Table 4.7.4 Collaborative Playlists: Invitations Modal
*   **User Interface:** Collaborative Playlists Peer Invitation System
*   **Blade View File:** Invite popup modal inside [show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/show.blade.php)
*   **Controller Endpoint:** [PlaylistController.php:L121-L146](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L121-L146) (`invite` method)
*   **Description:** Triggers invitations to collaborate. It fetches only mutual followers ($user->friends()$) in the dropdown menu to restrict collaborations to verified friends, sending an invitation notification to the selected user.

### Table 4.7.5 Collaborative Playlists: Pending Invitations & Lists
*   **User Interface:** Playlist Invitation Management & Playlists index
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php) (Pending invitations banner and lists)
*   **Controller Endpoint:** [PlaylistController.php:L148-L176](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L148-L176) (`acceptInvite` / `declineInvite` methods)
*   **Description:** A dashboard banner displays incoming collaborative invites, allowing users to "Accept" or "Decline". Below, the "Your Playlists" section lists active playlists with avatars of contributing collaborators.

### Table 4.7.6 Import: External Playlist (Manual URL)
*   **User Interface:** Spotify Manual URL Playlist Import
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/import/index.blade.php)
*   **Controller Endpoint:** [SpotifyImportController.php:L56-L124](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L56-L124) (`preview` method)
*   **Description:** A text input field where users paste public Spotify playlist URLs. It fetches track previews and limits selection to a maximum of 15 tracks. It features warning banners urging users to connect their library for direct integration.

### Table 4.7.7 Import: Library Integration (Linked Import)
*   **User Interface:** Spotify Connected Library playlist import
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/import/index.blade.php) (Library grid panel)
*   **Controller Endpoint:** [SpotifyImportController.php:L26-L54](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L26-L54) (`index` method)
*   **Description:** For linked users, this view displays their personal Spotify library playlists (fetching covers, metadata, and track counts in real-time) to import directly.

### Table 4.7.8 Playlist Import: Tracks Selection Page
*   **User Interface:** Curation: Selection & Fetching
*   **Blade View File:** [preview.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/import/preview.blade.php)
*   **Controller Endpoint:** [SpotifyImportController.php:L126-L188](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L126-L188) (`process` method)
*   **Description:** Shows individual tracks from the imported Spotify playlist. Users pick their absolute favorites (up to a **hard cap of 15 selected tracks**) using checkbox grid selectors.

---

## 4.8 User Profile Module

### Table 4.8.1 User Profile: Overview & Posts Activity
*   **User Interface:** User Profile: Overview
*   **Blade View File:** [show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/show.blade.php) / [profile.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/user/profile.blade.php)
*   **Controller Endpoint:** [UserProfileController.php:L14-L52](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php#L14-L52) (`show` method)
*   **Description:** Centered hub for profile configurations. Displays a customizable banner and avatar image, user statistics (followers, following, posts counts), and user identity badges. The default sub-tab displays a chronological listing of the user's shares.

### Table 4.8.2 Algorithmic Taste Analysis (Taste DNA)
*   **User Interface:** User Profile: Taste DNA
*   **Blade View File:** [taste.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/taste.blade.php)
*   **Controller Endpoint:** [UserProfileController.php:L54-L83](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php#L54-L83) (`taste` method)
*   **Description:** Provides visual breakdowns of a user's musical preferences. Renders a Genre DNA section containing percentage engagement progress bars, and a multi-axis Taste Radar canvas chart mapping their sonic fingerprint.

### Table 4.8.3 Curated Music Identity (Song Shelf Grid)
*   **User Interface:** Profile: Music Identity (Song Shelf)
*   **Blade View File:** [shelf.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php)
*   **Controller Endpoint:** [UserProfileController.php:L85-L108](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php#L85-L108) (`shelf` method)
*   **Description:** Displays the user's permanent 5-10 song shelf in a grid of cards (displaying artwork, title, and artist). This represents their public-facing music identity.

### Table 4.8.4 Song Shelf Editor Page
*   **User Interface:** Profile: Editing Music Identity
*   **Blade View File:** Modal editing container inside [shelf.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php)
*   **Controller Endpoint:** `App\Http\Controllers\UserShelfController` (`add`, `remove`, `reorder`)
*   **Description:** Opens a management interface containing song search components and a listing of their active shelf items. It includes drag-and-drop handles for reordering, delete buttons, and selection metrics (e.g. `5/10` status checks).

### Table 4.8.5 Saved Bookmarks List & Sync Button
*   **User Interface:** Profile: Saved Bookmarks & Spotify Sync
*   **Blade View File:** [saved.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/saved.blade.php)
*   **Controller Endpoint:** [UserProfileController.php:L110-L136](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php#L110-L136) (`saved` method)
*   **Description:** Curates posts the user has bookmarked. Includes a "Sync to Spotify" button at the top of the feed to batch export bookmarked tracks to their connected Spotify library.

### Table 4.8.6 Profile Followers Directory Page
*   **User Interface:** Profile Followers Directory List
*   **Blade View File:** [followers.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/followers.blade.php)
*   **Controller Endpoint:** [FollowerController.php:L38-L75](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FollowerController.php#L38-L75) (`followers` method)
*   **Description:** Omitted from initial lists. This page displays a paginated list of accounts following the target profile. It renders profile cards (avatars, handles, and connection counts) and includes quick-follow options.

### Table 4.8.7 Profile Following Directory Page
*   **User Interface:** Profile Following Directory List
*   **Blade View File:** [following.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/following.blade.php)
*   **Controller Endpoint:** [FollowerController.php:L77-L114](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FollowerController.php#L77-L114) (`following` method)
*   **Description:** Omitted from initial lists. This page displays a paginated list of accounts followed by the target profile. It includes connection toggles (allowing users to unfollow profiles directly).

### Table 4.8.8 User Search Results Directory Page
*   **User Interface:** Listener profiles search directory
*   **Blade View File:** [search-results.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/user/search-results.blade.php)
*   **Controller Endpoint:** [UserSearchController.php:L40-L75](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserSearchController.php#L40-L75) (`index` method)
*   **Description:** Omitted from initial lists. This directory displays user-specific search results when searching the platform for other music profiles, displaying usernames, emails, profile links, and direct follow/unfollow toggle actions.

---

## 4.9 Account Settings Module

### Table 4.9.1 Account Settings Dashboard
*   **User Interface:** Account Settings: Overview
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php)
*   **Controller Endpoint:** [SettingsController.php:L40-L76](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SettingsController.php#L40-L76) (`index` method)
*   **Description:** Account settings control panel. Divides credentials and connections into Email Verification status panels, Social Account Connections lists, and Personal Identity forms.

### Table 4.9.2 Email Verification Lifecycle
*   **User Interface:** Account Settings: Email Verification Flow
*   **Blade View File:** State block panels inside [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php)
*   **Description:** Manages the verification state. If verified, it renders a green "Verified" status card. If pending, it renders a warning notification panel alongside a resend link button to verify email address access.

### Table 4.9.3 Connected Social Accounts Connections
*   **User Interface:** Account Settings: Social Integration
*   **Blade View File:** Connections management container inside [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php)
*   **Controller Endpoint:** [SocialAuthController.php:L217-L241](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L217-L241) (`unlink` method)
*   **Description:** Manages connected accounts. Spotify and Google connections are displayed with statuses (Connected/Unlinked) alongside "Connect" and "Unlink" buttons, allowing users to toggle external API telemetry integration.

### Table 4.9.4 Personal Information Update Forms
*   **User Interface:** Profile Management: Identity Updates
*   **Blade View File:** Forms settings card inside [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php)
*   **Controller Endpoint:** [ProfileController.php:L32-L73](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ProfileController.php#L32-L73) (`update` method)
*   **Description:** Update forms for display name, avatar icons, banner backgrounds, and registration emails. It commits updates via individual section "Save" buttons.

### Table 4.9.5 Password Updates & Account Deletion
*   **User Interface:** Account Security & Data deletion
*   **Blade View File:** Security card layout inside [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php)
*   **Controller Endpoint:** [ProfileController.php:L84-L107](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ProfileController.php#L84-L107) (`destroy` method)
*   **Description:** Allows password updates (verifying current passwords) and account deletion. Account deletion removes the profile, shares, bookmarks, and comments, following a warning dialog prompt.

---

## 4.10 Admin Control & Catalog Moderation Module

### Table 4.10.1 Admin Dashboard Overview
*   **User Interface:** Admin Dashboard: Overview statistics
*   **Blade View File:** [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/dashboard.blade.php)
*   **Controller Endpoint:** [AdminController.php:L43-L64](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L43-L64) (`dashboard` method)
*   **Description:** High-level platform monitoring. Aggregates totals (users, shares, comments, catalog songs, playlists) and displays a weekly Shares Activity posting trends bar chart. Displays sidebars for new user registrations, top genres, and recent shares.

### Table 4.10.2 Admin User Management Directory
*   **User Interface:** Manage Users
*   **Blade View File:** [users.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/users.blade.php)
*   **Controller Endpoint:** [AdminController.php:L89-L113](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L89-L113) (`users` method)
*   **Description:** Directory displaying user lists (ID, user handles, emails, shares count, ban statuses, and join dates). Administrators search for accounts and perform Ban/Delete enforcements.

### Table 4.10.3 Admin Content Moderation System
*   **User Interface:** Content Moderation
*   **Blade View File:** [moderation.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/moderation.blade.php)
*   **Controller Endpoint:** [AdminController.php:L142-L160](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L142-L160) (`moderation` method)
*   **Description:** Content moderations panel. Offers side-by-side search tables for posts and comments, tracking author metadata, caption contents, upvotes/likes, and deletion tools.

### Table 4.10.4 Admin Access Management (Manage Admins)
*   **User Interface:** Manage Admins
*   **Blade View File:** [admins.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/admins.blade.php)
*   **Controller Endpoint:** [AdminController.php:L186-L200](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L186-L200) (`admins` method)
*   **Description:** Access control panel. Displays active staff profiles, highlights active sessions, and provides forms to add new administrators (Full Name, Email, and minimum 8-character Password) or revoke access.

### Table 4.10.5 AI Recommendation Engine Preview & Retrain Tool
*   **User Interface:** AI Recs Preview
*   **Blade View File:** [retrain.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/retrain.blade.php)
*   **Controller Endpoint:** [AdminController.php:L218-L230](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L218-L230) (`retrainPage` method)
*   **Description:** Recommender service inspector. Displays Flask service health status, a "Force Retrain Model" action, and dropdown user selectors to preview 50 recommended tracks with scores, reasons, and SVD/Context/Social boost breakdowns.

### Table 4.10.6 Admin Profile Details Panel
*   **User Interface:** Admin Profile
*   **Blade View File:** [profile.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/profile.blade.php)
*   **Controller Endpoint:** [AdminController.php:L247-L252](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L247-L252) (`profile` method)
*   **Description:** Staff credentials editor. Allows administrators to update their display names, edit registered emails, and modify dashboard passwords.

### Table 4.10.7 Recommendation Engine Test Suite (Accuracy Framework)
*   **User Interface:** Recsys Test Suite
*   **Blade View File:** [algo_test_suite.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/algo_test_suite.blade.php)
*   **Controller Endpoint:** [AdminController.php:L283-L287](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L283-L287) (`algoTestSuite` method)
*   **Description:** Runs automated recsys checks (TC-01 through TC-08). Outputs mathematical verification logs for TF-IDF calculations, SVD rating flattening, social trust calculations, and SVD model cross-validation benchmarks.

### Table 4.10.8 Admin Songs Catalog Directory
*   **User Interface:** Songs Catalog Directory
*   **Blade View File:** [index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/songs/index.blade.php)
*   **Controller Endpoint:** [AdminController.php:L320-L330](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L320-L330) (`songs` method)
*   **Description:** Omitted from initial lists, this directory displays the catalog of ingested database songs. It lists tracks (titles, artists, genres, album covers, release dates, and YouTube mapping statuses) and provides manual deletion actions.

### Table 4.10.9 Admin Song Manual Ingestion Page
*   **User Interface:** Add New Song manually
*   **Blade View File:** [create.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/songs/create.blade.php)
*   **Controller Endpoint:** [AdminController.php:L332-L337](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L332-L337) (`createSong` method)
*   **Description:** Omitted from initial lists, this form allows administrators to manually insert songs by pasting raw Spotify Track ID strings. It queries Spotify APIs and provisions local metadata records.

### Table 4.10.10 Admin Song Editor & Metadata Refetcher
*   **User Interface:** Edit Song
*   **Blade View File:** [edit.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/songs/edit.blade.php)
*   **Controller Endpoint:** [AdminController.php:L347-L352](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php#L347-L352) (`editSong` method)
*   **Description:** Omitted from initial lists, this form allows editing catalog records (title, artists, cover URL, and genres JSON arrays). It includes a "Refetch Metadata" button to override values by querying Spotify, Discogs, and MusicBrainz APIs.
