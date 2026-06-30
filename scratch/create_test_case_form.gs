/**
 * RESO - System Functionality Test Forms Generator
 *
 * Organized page-by-page following the website UI structure.
 * Designed for a single tester to prevent account clutter and eliminate reliance on User B.
 * Run createForm() to generate the form.
 */
function createForm() {
  var form = FormApp.create('RESO - System Functionality Test Form');
  form.setDescription(
    'This form is used to log the manual testing results of the RESO platform.\n' +
    'Please proceed sequentially page-by-page. For each test, mark Pass or Fail, and add observations if needed.'
  );

  // General Metadata
  form.addDateItem().setTitle('Date of Testing').setRequired(true);
  form.addTextItem().setTitle('Tester Name').setRequired(true);
  form.addTextItem().setTitle('Position/Role').setRequired(true);

  var modules = [
    {
      section: 'PHASE 1 (UNLINKED): General User Auth & Onboarding',
      tests: [
        {
          id: 'TC-01: Standard User Registration',
          desc:
            'Steps:\n' +
            '1. Navigate to /register.\n' +
            '2. Fill in the manual registration form requiring Username, Email, Profile Picture upload, and Password confirmation.\n' +
            '3. Click Register.\n\n' +
            'Expected UI: Form validation prompts for required inputs. Redirects to "Verify your email" page showing an envelope icon, a primary "Resend Email" button, and secondary "Skip for now" / "Log Out" options.'
        },
        {
          id: 'TC-02: Email Verification',
          desc:
            'Pre-condition: Registered via TC-01.\n\n' +
            'Steps:\n' +
            '1. Open your email inbox.\n' +
            '2. Click the activation link in the verification email.\n\n' +
            'Expected UI: Redirects directly to the onboarding page ("Let\'s build your taste profile") displaying the genre and song curation shelf.'
        },
        {
          id: 'TC-03: Taste Profile Onboarding',
          desc:
            'Steps:\n' +
            '1. On the onboarding page, select favorite tracks using the search bar or genre pills.\n' +
            '2. Observe the dynamic track slots and counters.\n' +
            '3. Try to complete onboarding with fewer than 5 songs.\n' +
            '4. Select between 5 and 10 tracks, and click "Complete Onboarding".\n\n' +
            'Expected UI: Selected tracks populate dashed slots with album art. The counter badge shifts dynamically from X/5 to X/10. The "Complete Onboarding" button is disabled until the 5-track minimum is met. Clicking it redirects to the feed.'
        }
      ]
    },
    {
      section: 'PHASE 1 (UNLINKED): Home Feed & Core Social Interactions',
      tests: [
        {
          id: 'TC-04: Search & Share a Song',
          desc:
            'Steps:\n' +
            '1. Go back to the Home feed (click "Home" in the sidebar).\n' +
            '2. Click the central input in the "Drop a track" composer.\n' +
            '3. Type a song name, select a track from the search dropdown, and add a caption.\n' +
            '4. Toggle the mode to "Just Sharing" and click Post.\n' +
            '5. Verify sidebar page links, follow button in "Who to Follow" sidebar, and "Explore" vs "Following" feed tabs.\n\n' +
            'Expected UI: Published post appears at top of feed immediately. Left sidebar links highlight active states. Taste Neighbors (Who to Follow) follows instantly. Explore vs Following tabs toggle correctly.'
        },
        {
          id: 'TC-05: Seeking Recommendations (Ask & Suggest)',
          desc:
            'Steps:\n' +
            '1. Open the post composer, select a track, add a caption, toggle to "Asking for Recommendations", and click Post.\n' +
            '2. Locate your post in the feed and verify the badge.\n' +
            '3. Click comments on your post, use the integrated Spotify search widget in the comment block, select a track, and submit.\n\n' +
            'Expected UI: The composer prompt shifts to "What track should I hear next?". The card displays a blue "SEEKING RECOMMENDATIONS" badge. Suggesting a track via the search widget embeds a playable song card in the comment thread.'
        },
        {
          id: 'TC-06: Song Card Actions (Unlinked)',
          desc:
            'Pre-condition: Spotify is NOT connected.\n\n' +
            'Steps:\n' +
            '1. Hover over a song card in the feed.\n' +
            '2. Click the YouTube button.\n' +
            '3. Click the Spotify button.\n' +
            '4. Click the "+" button, then select "Spotify Playlist" and "Reso Playlist".\n\n' +
            'Expected UI:\n' +
            '- YouTube opens a search tab for the track.\n' +
            '- Spotify loads an inline preview player below the card (playing a 30s preview).\n' +
            '- Selecting "Spotify Playlist" triggers a warning modal. Selecting "Reso Playlist" adds the track.'
        },
        {
          id: 'TC-07: Like & Bookmark Posts',
          desc:
            'Steps:\n' +
            '1. Click the Like icon on a post.\n' +
            '2. Click the Bookmark icon on a post.\n\n' +
            'Expected UI: Like count increments instantly and highlights. Bookmarked post is saved to your profile.'
        },
        {
          id: 'TC-08: Not for Me (Dislike)',
          desc:
            'Steps:\n' +
            '1. Click the three-dot dropdown on a feed card and select "Not for me".\n' +
            '2. Open the menu again.\n\n' +
            'Expected UI: Dislike registers (resets active like count if set). Dropdown option text shifts to "Undo Not For Me" with active styling.'
        },
        {
          id: 'TC-09: Commenting & Nested Replies',
          desc:
            'Steps:\n' +
            '1. Open comments on a post. Type a comment and submit.\n' +
            '2. Click Upvote next to your comment. <br>3. Reply to your own comment. <br>4. Delete your parent comment.\n\n' +
            'Expected UI: Comment count increments instantly. Upvote count increases. Reply field supports nested replies. Deleting a parent comment changes its text to "[deleted]".'
        },
        {
          id: 'TC-10: Global Search & Filtering',
          desc:
            'Steps:\n' +
            '1. Type a keyword in the global search bar in the top navbar and press Enter.\n' +
            '2. Toggle between the "Users" and "Posts" search tabs.\n\n' +
            'Expected UI: Search view loads. Users tab lists matched profiles with direct follow toggles. Posts tab surfaces matching content with rich media cards and status badges.'
        }
      ]
    },
    {
      section: 'PHASE 1 (UNLINKED): Profile, Playlists & Settings',
      tests: [
        {
          id: 'TC-11: Profile Tabs & Brand Identity',
          desc:
            'Steps:\n' +
            '1. Go to your Profile. Check customized branding, username, and habit-based badges.\n' +
            '2. Toggle through the tabs: "Posts", "Taste DNA", "Song Shelf", and "Saved".\n\n' +
            'Expected UI: Posts shows chronological shared songs. Taste DNA displays the Genre DNA progress bars. Saved tab displays bookmarked posts.'
        },
        {
          id: 'TC-12: Editing Music Identity (Song Shelf)',
          desc:
            'Steps:\n' +
            '1. Under the "Song Shelf" tab, click "Edit Shelf".\n' +
            '2. Remove a song, search and add another song using the search component.\n' +
            '3. Try saving with fewer than 5 songs or more than 10.\n' +
            '4. Click "Done Editing".\n\n' +
            'Expected UI: Opens search panel to manage shelf. System enforces 5–10 track limit. Clicking "Done Editing" updates the profile grid.'
        },
        {
          id: 'TC-13: Personalized Recommendations & Sync Warning (Unlinked)',
          desc:
            'Pre-condition: User is NOT connected to Spotify.\n\n' +
            'Steps:\n' +
            '1. Go to the Discover page.\n' +
            '2. Hover over recommendation cards and test quick-access playback/curation icons.\n' +
            '3. Click the "LIKE" and "PASS" feedback loop buttons on the recommendation cards.\n' +
            '4. Click "Sync to Spotify" in the header.\n\n' +
            'Expected UI: Grid of personalized recommendations loads. "LIKE" reinforces taste profile, "PASS" excludes the track. "Sync to Spotify" is visible and clicking it triggers a warning prompt.'
        },
        {
          id: 'TC-14: Playlist Creation & Invites',
          desc:
            'Steps:\n' +
            '1. Go to /playlists, click "New Playlist", enter name and description, and click Create.\n' +
            '2. On the playlist details page, click "Invite" (verify followers selection list).\n' +
            '3. Click the cover image area, upload an image (<2MB), and click Save.\n' +
            '4. Search for a song inside the page search box, add it, then click trash icon next to it to remove.\n\n' +
            'Expected UI: Playlist is created showing contributor list with your avatar. Invite modal displays followers (placeholder if none). Cover photo updates instantly. Adding and removing songs is immediate.'
        },
        {
          id: 'TC-15: Profile Branding Customization',
          desc:
            'Steps:\n' +
            '1. Go to Settings.\n' +
            '2. Upload a profile avatar (<2MB) and click Save.\n' +
            '3. Upload a profile banner (<4MB) and click Save.\n' +
            '4. Change display name and primary email and click Save.\n\n' +
            'Expected UI: Avatar and banner update instantly across the site. Changing email resets verification status to unverified.'
        }
      ]
    },
    {
      section: 'PHASE 2: Social Account Connections',
      tests: [
        {
          id: 'TC-16: Spotify & Google Connections',
          desc:
            'Steps:\n' +
            '1. Under Connected Accounts, click "Link Spotify Account". Authorise on Spotify pop-up screen (Important: You must log into Spotify using the specific email address you sent to the developer to be whitelisted).\n' +
            '2. Under Connected Accounts, click "Connect Google". Authorise with Google account.\n' +
            '3. Log out. Log in on /login via "Continue with Google" or "Sign in with Spotify".\n\n' +
            'Expected UI: Settings show Connected status. Social logins succeed and bypass password forms.'
        }
      ]
    },
    {
      section: 'PHASE 3 (LINKED): Spotify-Specific Features & Integration',
      tests: [
        {
          id: 'TC-17: Song Card Actions - Add to Spotify Playlist (Linked)',
          desc:
            'Pre-condition: Spotify is connected.\n\n' +
            'Steps:\n' +
            '1. Hover over a song card in the feed.\n' +
            '2. Click the "+" button, then select "Spotify Playlist".\n\n' +
            'Expected UI: Opens the "Add to Spotify Playlist" modal, allowing you to select an existing Spotify playlist or create a new one to add the track to.'
        },
        {
          id: 'TC-18: Profile Bookmarks - Spotify Sync (Linked)',
          desc:
            'Pre-condition: Spotify is connected and you have saved/bookmarked posts.\n\n' +
            'Steps:\n' +
            '1. Go to Profile -> "Saved" tab.\n' +
            '2. Verify that the green "Sync to Spotify" button is now visible.\n' +
            '3. Click the "Sync to Spotify" button.\n\n' +
            'Expected UI: Sync to Spotify creates a private playlist titled "Reso Bookmarks" (with "Created via Reso" description) in your Spotify account.'
        },
        {
          id: 'TC-19: Discovery Spotify Sync (Linked)',
          desc:
            'Pre-condition: Spotify is connected.\n\n' +
            'Steps:\n' +
            '1. Go to the Discover page.\n' +
            '2. Click "Sync to Spotify" in the header.\n\n' +
            'Expected UI: Sync to Spotify creates a private playlist titled "Reso Discoveries" (with "Created via Reso" description) in your Spotify account.'
        },
        {
          id: 'TC-20: Spotify Playlist Library Import (Linked)',
          desc:
            'Pre-condition: Spotify is connected.\n\n' +
            'Steps:\n' +
            '1. Under /playlists click "Import Spotify".\n\n' +
            'Expected UI: The page automatically pulls and lists your Spotify playlists from your connected Spotify library under "Your Spotify Playlists". Clicking one fills in the Spotify URL input and submits automatically.'
        }
      ]
    },
    {
      section: 'PHASE 4: Notifications & Admin Operations',
      tests: [
        {
          id: 'TC-21: Welcome & Interaction Notifications',
          desc:
            'Steps:\n' +
            '1. Check the notification bell immediately after onboarding.\n' +
            '2. In an incognito window, log in as Admin. Go to Users, search and click "Follow" next to your test account.\n' +
            '3. In your primary session, check the notification bell, click it, and mark all as read.\n\n' +
            'Expected UI: Welcome notification displays. Follow notification shows up with red badge count. Mark read clears the badge.'
        },
        {
          id: 'TC-22: Admin Login & Dashboard Overview',
          desc:
            'Steps:\n' +
            '1. Log out. Go to /login/admin.\n' +
            '2. Log in using admin1@musicsocial.com and AdminPassword123!.\n' +
            '3. Verify dashboard statistics and 7-day activity graph fit laptop monitors cleanly without overlapping.\n\n' +
            'Expected UI: Redirects to /admin dashboard. Left sidebar and right content blocks scale cleanly. Status cards format matches monitor size.'
        },
        {
          id: 'TC-23: User Management',
          desc:
            'Steps:\n' +
            '1. Go to Admin -> Users.\n' +
            '2. Search for your test account by name or email.\n' +
            '3. Click "Ban" next to the user. Open incognito and verify login is blocked.\n' +
            '4. Click "Unban" next to the user. Verify login is restored.\n' +
            '5. Click "Delete" next to the user.\n\n' +
            'Expected UI: Displays user details (ID, handle, email, share count, status, join date). Search works. Banned status blocks logins. Deleting permanently purges the user.'
        },
        {
          id: 'TC-24: Song Catalog Management',
          desc:
            'Steps:\n' +
            '1. Go to Admin -> Songs.\n' +
            '2. Search and sort songs.\n' +
            '3. Click "Add New Song", enter metadata/Spotify ID, and submit.\n' +
            '4. Click "Edit" next to a song, edit metadata, and save.\n' +
            '5. Click "Refetch Genres" on a song.\n' +
            '6. Click "Delete" on a song.\n\n' +
            'Expected UI: Catalog lists tracks, artists, and genres. Search, edit, delete, and refetch actions update the database instantly.'
        },
        {
          id: 'TC-25: Moderation Streams',
          desc:
            'Steps:\n' +
            '1. Go to Admin -> Moderation.\n' +
            '2. Verify the 3 rows: Shares, Comments, and Playlists (decluttered layout).\n' +
            '3. Filter shares and comments by user/keyword using the search bar.\n' +
            '4. Click "Delete" next to a Share, Comment, or Playlist.\n\n' +
            'Expected UI: Searching works. Delete actions immediately purge violating records.'
        },
        {
          id: 'TC-26: Algo Recs Preview & Retraining',
          desc:
            'Steps:\n' +
            '1. Go to Admin -> Retrain.\n' +
            '2. Check the Recommender Service status badge.\n' +
            '3. Click "Force Retrain Model".\n' +
            '4. Select a user from the dropdown to preview their recommendation feed.\n\n' +
            'Expected UI: Status shows active. Model retrains successfully. Recommendation preview table displays recommended tracks showing Rank, Song title, and Reasoning.'
        }
      ]
    },
    {
      section: 'PHASE 5: Destructive Actions',
      tests: [
        {
          id: 'TC-27: Permanent Account Deletion',
          desc:
            'Steps:\n' +
            '1. Log out from the Admin session.\n' +
            '2. Login back to the test user account.\n' +
            '3. Under Account Security in Settings, click "Delete Account".\n' +
            '4. Enter your current password and click confirm.\n\n' +
            'Expected UI: Warning prompt displays. All user data, resources, and links are permanently deleted. Session terminates.'
        }
      ]
    }
  ];

  _buildFormSections(form, modules);

  Logger.log('Merged form ready!');
  Logger.log('Edit URL:    ' + form.getEditUrl());
  Logger.log('Respond URL: ' + form.getPublishedUrl());
}

function _buildFormSections(form, modules) {
  for (var m = 0; m < modules.length; m++) {
    form.addPageBreakItem().setTitle(modules[m].section);

    var tests = modules[m].tests;
    for (var t = 0; t < tests.length; t++) {
      var tc = tests[t];

      form.addMultipleChoiceItem()
        .setTitle(tc.id)
        .setHelpText(tc.desc)
        .setChoiceValues(['Pass', 'Fail'])
        .setRequired(true);

      form.addParagraphTextItem()
        .setTitle(tc.id + ' - Remarks / Observations (Optional)')
        .setRequired(false);
    }
  }
}
