# Reso – Beginner's UI & Code Guide (All Pages + Exact Code Sections)

> This guide shows every page, every button, and the **exact lines of code** in your real files that make it work. Think of it as a map: **UI button → what code runs → where that code lives (line number)**.

---

## How to Read This Guide

Every feature follows this pattern:

```
🔘 Button/Feature Name
   ├── What it does (plain English)
   ├── 📄 Frontend File  → exact file + line numbers
   ├── 🗺️  Route         → URL + routes/web.php line
   ├── ⚙️  Controller    → PHP method that runs
   └── 💻 Actual Code snippet (copied from your real files)
```

---

---

# 🌐 Page 0: Landing / Welcome Page (`/`)

**File:** [`resources/views/welcome.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php)  
**Route:** [`routes/web.php` lines 18–26](file:///c:/laragon/www/musicsocial-main/routes/web.php#L18-L26)

---

### 🔘 Logo & Navigation Bar
- **File + Lines:** [`welcome.blade.php` L62–L93](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L62-L93)
- **What it does:** Fixed top navbar with the Reso logo, dark/light mode toggle, and Login/Sign up links.

```html
<!-- welcome.blade.php L62–L76 -->
<nav class="fixed w-full z-50 top-0 bg-slate-50/80 dark:bg-black/80 backdrop-blur-md ...">
    <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <img src="{{ asset('icons/reso.png') }}" class="h-8 w-auto object-contain" alt="Reso Logo">
        <span class="font-bold text-xl tracking-tight">Reso</span>

        <!-- Dark/Light Mode Toggle Button -->
        <button onclick="toggleTheme()" class="text-gray-500 hover:text-gray-900 ...">
            <!-- Sun icon (shows in dark mode) / Moon icon (shows in light mode) -->
        </button>
    </div>
</nav>
```

---

### 🔘 Dark/Light Mode Toggle (JavaScript Function)
- **File + Lines:** [`welcome.blade.php` L16–L24](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L16-L24)
- **What it does:** Toggles CSS class `dark` on the root HTML element and saves preference in browser localStorage.

```javascript
// welcome.blade.php L21–L24
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
```

---

### 🔘 "Create Account" / "Sign up" Button
- **File + Lines:** [`welcome.blade.php` L83–L86](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L83-L86) and [`L122–L124`](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L122-L124)
- **Route:** `GET /register` → handled by Laravel Breeze auth package.

```html
<!-- welcome.blade.php L83–L86 (navbar) -->
<a href="{{ route('register') }}"
   class="px-5 py-2.5 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold">
    Sign up
</a>

<!-- welcome.blade.php L122–L124 (hero button) -->
<a href="{{ route('register') }}"
   class="w-full sm:w-auto px-8 py-4 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 ...">
    Create Account
</a>
```

---

### 🔘 "Log in" Button
- **File + Lines:** [`welcome.blade.php` L126–L128](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L126-L128)
- **Route:** `GET /login` → Laravel Breeze [`routes/auth.php`](file:///c:/laragon/www/musicsocial-main/routes/auth.php)

```html
<!-- welcome.blade.php L126–L128 -->
<a href="{{ route('login') }}"
   class="w-full sm:w-auto px-8 py-4 rounded-full bg-transparent border border-gray-300 ...">
    Log in
</a>
```

---

### 🔘 Hero Section (Animated App Mockup Preview Card)
- **File + Lines:** [`welcome.blade.php` L134–L201](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L134-L201)
- **What it does:** A static demo card showing a fake music post. No server request — it's decorative HTML only.

```html
<!-- welcome.blade.php L138 -->
<div class="bg-white dark:bg-card border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl p-6 ...">
    <!-- Fake username -->
    <div class="font-bold text-sm">Marcus</div>
    <!-- Fake taste match badge -->
    <div class="text-brand text-xs ... bg-brand/10 rounded-full">94% Taste Match</div>
    <!-- Fake album art and play/like icons are rendered here, no JS logic -->
</div>
```

---

### 🔘 Features Grid Section ("Share Your Rotation", "Find Taste Neighbors", "Seamless Curation")
- **File + Lines:** [`welcome.blade.php` L207–L244](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php#L207-L244)
- **What it does:** Static marketing section — three feature cards with icons and descriptions. No backend code.

```html
<!-- welcome.blade.php L216–L219 -->
<h3 class="text-xl font-bold mb-3">Share Your Rotation</h3>
<p class="text-gray-600 dark:text-gray-400 leading-relaxed">
    Share any track with a caption. Your network can upvote, save, or mark tracks as 'not for me' ...
</p>
```

---

---

# 📰 Page 1: Dashboard / Home Feed (`/dashboard`)

**File:** [`resources/views/dashboard.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php)  
**Route:** [`routes/web.php` L87–L88](file:///c:/laragon/www/musicsocial-main/routes/web.php#L87-L88)  
**Controller:** [`app/Http/Controllers/FeedController.php`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php)

---

### 🔘 "Following" / "Explore" Feed Tabs
- **File + Lines:** [`dashboard.blade.php` L21–L30](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php#L21-L30)
- **Route:** `GET /dashboard?feed=following` or `GET /dashboard?feed=explore` → [`web.php` L87](file:///c:/laragon/www/musicsocial-main/routes/web.php#L87)
- **Controller Action:** [`FeedController@index` L46–L68](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L46-L68)

```html
<!-- dashboard.blade.php L22–L29 — The two tab links -->
<a href="{{ route('dashboard', ['feed' => 'following']) }}" wire:navigate
   class="pb-3 text-lg font-bold transition-colors border-b-2
   {{ $feedType === 'following' ? 'text-gray-900 dark:text-white border-custom-mid-blue' : 'text-gray-400' }}">
    Following
</a>
<a href="{{ route('dashboard', ['feed' => 'explore']) }}" wire:navigate
   class="...{{ $feedType === 'explore' ? 'border-custom-mid-blue' : 'border-transparent' }}">
    Explore
</a>
```

```php
// FeedController.php L46–L68 — What runs when you click a tab
$feedType = request('feed', 'following');

if ($feedType === 'explore') {
    // Pull random shares from ALL users
    $shares = Share::where('is_deleted', false)->inRandomOrder()->paginate(20);
} else {
    // Only get shares from people you follow
    $followingIds = $user->following()->pluck('id');
    $shares = Share::where('is_deleted', false)
                   ->where(function ($query) use ($followingIds, $user) {
                       $query->whereIn('user_id', $followingIds)
                             ->orWhere('user_id', $user->id);
                   })
                   ->latest()
                   ->paginate(20);
}
```

---

### 🔘 Post Composer – "Just Sharing" / "Asking for Recommendations" Toggle
- **File + Lines:** [`components/post-composer.blade.php` L175–L193](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L175-L193)
- **What it does:** Toggles Alpine variable `isSeekingRecommendations` which changes the post type sent to backend (`music` or `recommendation_request`).

```html
<!-- post-composer.blade.php L180–L189 — The toggle switch button -->
<button type="button"
        @click="isSeekingRecommendations = !isSeekingRecommendations"
        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full ..."
        :class="isSeekingRecommendations ? 'bg-custom-mid-blue' : 'bg-gray-200'">
    <!-- The sliding circle inside the toggle -->
    <span :class="isSeekingRecommendations ? 'translate-x-5' : 'translate-x-0'"
          class="inline-block h-5 w-5 rounded-full bg-white shadow transform ...">
    </span>
</button>
<span :class="isSeekingRecommendations && 'text-custom-mid-blue font-bold'">
    Asking for Recommendations
</span>
```

---

### 🔘 Post Composer – Spotify Song Search Box
- **File + Lines:** [`components/post-composer.blade.php` L1–L13](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L1-L13) (Alpine state), [`L49–L70`](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L49-L70) (search function), [`L209–L216`](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L209-L216) (input field)
- **Route:** `GET /search/tracks?query=...` → [`web.php` L82](file:///c:/laragon/www/musicsocial-main/routes/web.php#L82)
- **Controller:** [`SpotifySearchController@search`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifySearchController.php)

```javascript
// post-composer.blade.php L49–L70 — search() runs when you type 3+ characters
search() {
    if (this.searchQuery.length < 3) {
        this.searchResults = [];
        return;
    }
    this.loading = true;
    fetch(`{{ route('spotify.search') }}?query=${encodeURIComponent(this.searchQuery)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        this.searchResults = data;  // Results appear as a dropdown
        this.loading = false;
    });
}
```

```html
<!-- post-composer.blade.php L209–L216 — The actual input box -->
<input
    type="text"
    x-model.debounce.300ms="searchQuery"   <!-- Auto-triggers search() after 300ms -->
    @focus="fetchRecent(); showRecent = true"  <!-- Shows recently played on focus -->
    :placeholder="isSeekingRecommendations
        ? 'Search for a track you want similar suggestions for...'
        : 'Search for a song...'"
/>
```

---

### 🔘 Post Composer – "Drop It" Search Button
- **File + Lines:** [`components/post-composer.blade.php` L219–L227](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L219-L227)

```html
<!-- post-composer.blade.php L219–L227 -->
<button type="button"
        @click="searchQuery ? search() : null"
        :disabled="!searchQuery"
        :class="searchQuery.length > 0
            ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg'
            : 'bg-transparent text-gray-300 cursor-not-allowed'"
        class="rounded-full px-5 py-2 font-bold transition-all text-sm">
    Drop It
</button>
```

---

### 🔘 Post Composer – Submit "Post Song" Button (submitPost)
- **File + Lines:** [`components/post-composer.blade.php` L97–L140](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L97-L140) (JS function), [`L164`](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php#L164) (form tag)
- **Route:** `POST /shares` → [`web.php` L98](file:///c:/laragon/www/musicsocial-main/routes/web.php#L98)
- **Controller:** [`ShareController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php)

```javascript
// post-composer.blade.php L97–L140 — submitPost() sends the track to backend
submitPost() {
    if (!this.selectedTrack) return;
    this.loading = true;

    const formData = new FormData();
    // 'music' or 'recommendation_request' depending on the toggle above
    formData.append('type', this.isSeekingRecommendations ? 'recommendation_request' : this.postType);
    formData.append('spotify_track_id', this.selectedTrack.id);
    formData.append('caption', this.$refs.captionInput.value);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("shares.store") }}', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            // Inject the new share card at the TOP of the feed without a page reload
            document.getElementById('feed-container').insertAdjacentHTML('afterbegin', data.html);
            this.resetComposer();
        }
    });
}
```

---

### 🔘 Share Card – Like ❤️ Button
- **File + Lines:** [`components/share-card.blade.php` L426–L474](file:///c:/laragon/www/musicsocial-main/resources/views/components/share-card.blade.php#L426-L474)
- **Route:** `POST /shares/{share}/like` → [`web.php` L101](file:///c:/laragon/www/musicsocial-main/routes/web.php#L101)
- **Controller:** [`LikeController@toggle`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/LikeController.php)

```html
<!-- share-card.blade.php L426–L474 — Like Button Zone -->
<form @submit.prevent="
    liked = !liked;                    <!-- Toggle local state immediately (optimistic UI) -->
    liked ? likesCount++ : likesCount--;
    if (liked && disliked) {
        disliked = false;              <!-- Remove dislike if switching to like -->
    }
    fetch('{{ route('shares.like', $share) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => { if (data.disliked !== undefined) disliked = data.disliked; })
" class="flex justify-center">
    <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-pink-50 ...">
        <!-- Filled heart when liked (x-if="liked") -->
        <!-- Empty heart when not liked (x-if="!liked") -->
        <span x-text="likesCount" class="text-sm font-bold"></span>
    </button>
</form>
```

---

### 🔘 Share Card – Bookmark 🔖 Button
- **File + Lines:** [`components/share-card.blade.php` L488–L519](file:///c:/laragon/www/musicsocial-main/resources/views/components/share-card.blade.php#L488-L519)
- **Route:** `POST /shares/{share}/bookmark` → [`web.php` L103](file:///c:/laragon/www/musicsocial-main/routes/web.php#L103)
- **Controller:** [`ShareController@toggleBookmark`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php)

```html
<!-- share-card.blade.php L489–L505 — Bookmark Button -->
<form @submit.prevent="
    bookmarked = !bookmarked;
    fetch('{{ route('shares.bookmark', $share) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(response => { if (!response.ok) bookmarked = !bookmarked; }); // Revert on error
">
    <button type="submit" class="... hover:bg-yellow-50 ...">
        <!-- Filled bookmark (yellow) when bookmarked, empty when not -->
    </button>
</form>
```

---

### 🔘 Share Card – 💬 Comments Button & Section Toggle
- **File + Lines:** [`components/share-card.blade.php` L477–L485](file:///c:/laragon/www/musicsocial-main/resources/views/components/share-card.blade.php#L477-L485)
- **What it does:** Displays the comment bubble icon and live comment count (`$share->comments->count()`). Clicking toggles the Alpine state `commentsOpen = !commentsOpen` to show or hide the post's comment section.

```html
<!-- share-card.blade.php L477–L485 — Comment toggle button -->
<button @click="commentsOpen = !commentsOpen" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-blue-50 ...">
    <svg ...></svg>
    <span class="text-sm font-bold text-gray-600">{{ $totalCount ?? $share->comments->count() }}</span>
</button>
```

---

### 🔘 Share Card – 📝 Comment Input & Submission Form
- **Frontend File + Lines:** [`components/share-card.blade.php` L630–L670](file:///c:/laragon/www/musicsocial-main/resources/views/components/share-card.blade.php#L630-L670)
- **Route:** `POST /shares/{share}/comments` → [`routes/web.php` L99](file:///c:/laragon/www/musicsocial-main/routes/web.php#L99)
- **Controller:** [`CommentController@store` L45–L120](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L45-L120)
- **What it does:** Allows users to write a comment on any post. Submitting sends an AJAX request (`fetch`) to `CommentController@store`. It automatically validates input, parses `@mentions`, checks for Spotify track URLs, creates a `Comment` record, sends notifications, and injects the new comment DOM element without reloading the page.

```html
<!-- share-card.blade.php L630–L665 — Comment Form -->
<form @submit.prevent="
    if(!newComment.trim() || submitting) return;
    submitting = true;
    fetch('{{ route('shares.comments.store', $share) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ body: newComment })
    })
    .then(res => res.text())
    .then(html => {
        $refs.commentsList.insertAdjacentHTML('beforeend', html);
        newComment = '';
        submitting = false;
    });
">
    <input type="text" x-model="newComment" placeholder="Add a comment..." class="..." />
    <button type="submit" :disabled="submitting">Post</button>
</form>
```

```php
// CommentController.php L45–L82 — Storing a new comment
public function store(Request $request, Share $share)
{
    $validated = $request->validate([
        'body' => 'required|string|max:1000',
        'parent_id' => 'nullable|exists:comments,id',
    ]);

    // Auto-Detect Spotify Track in Comment Body
    if (preg_match('/open\.spotify\.com\/track\/([a-zA-Z0-9]+)/i', $validated['body'], $matches)) {
        $trackData = $this->spotifyService->getTrack($matches[1]);
        if (isset($trackData['song'])) {
            $validated['body'] .= " [SONG:{$trackData['song']->spotify_track_id}]";
        }
    }

    $comment = $share->comments()->create([
        'user_id' => auth()->id(),
        'body' => $validated['body'],
    ]);

    return view('components.comment', ['comment' => $comment])->render();
}
```

---

### 🔘 Comment Component – 💬 Nested Replies, Upvotes & Embedded Spotify Music
- **Frontend File + Lines:** [`components/comment.blade.php` L1–L150](file:///c:/laragon/www/musicsocial-main/resources/views/components/comment.blade.php#L1-L150)
- **Controller Methods:**
  - Upvote: [`CommentController@toggleUpvote` L239–L266](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L239-L266)
  - Edit: [`CommentController@update` L152–L176](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L152-L176)
  - Delete: [`CommentController@destroy` L185–L214](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L185-L214)
- **What it does:** Renders an individual comment with avatar, timestamp, upvote button, reply form, edit box, and auto-embedded Spotify music card.

```javascript
// components/comment.blade.php L1–L39 — Alpine component logic for comments
x-data="{
    openReply: false,
    openEdit: false,
    bodyText: @js($comment->getCleanBody()),
    isDeleted: {{ $comment->body === '[deleted]' ? 'true' : 'false' }},
    upvoted: {{ $comment->hasUpvoted(auth()->id()) ? 'true' : 'false' }},
    upvoteCount: {{ $comment->getUpvoteCount() }},
    songId: '{{ $comment->getEmbeddedSongId() }}',
    
    toggleUpvote() {
        fetch('{{ route('shares.comments.upvote', ['share' => $comment->share, 'comment' => $comment]) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            this.upvoted = data.upvoted;
            this.upvoteCount = data.count;
        });
    }
}"
```

```php
// CommentController.php L239–L266 — Toggle comment upvotes
public function toggleUpvote(Share $share, Comment $comment)
{
    $userId = auth()->id();
    $body = $comment->body;

    if (preg_match('/\[UPVOTES:([^\]]*)\]/', $body, $matches)) {
        $ids = array_filter(explode(',', $matches[1]));
        if (in_array((string)$userId, $ids)) {
            $ids = array_diff($ids, [(string)$userId]); // Remove upvote
        } else {
            $ids[] = (string)$userId; // Add upvote
        }
        $body = preg_replace('/\[UPVOTES:[^\]]*\]/', "[UPVOTES:" . implode(',', $ids) . "]", $body);
    } else {
        $body .= " [UPVOTES:{$userId}]";
    }

    $comment->update(['body' => $body]);

    return response()->json([
        'upvoted' => $comment->fresh()->hasUpvoted($userId),
        'count' => $comment->fresh()->getUpvoteCount(),
    ]);
}
```

---

### 🔘 Share Card – Feed Container (where new posts appear)
- **File + Lines:** [`dashboard.blade.php` L73–L86](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php#L73-L86)
- **What it does:** The `id="feed-container"` div is the target where `submitPost()` injects new share cards without reloading the page.

```html
<!-- dashboard.blade.php L73–L86 -->
<div id="feed-container" class="space-y-6">
    @forelse ($shares as $share)
        <x-share-card :share="$share" />   <!-- Renders one share card per post -->
    @empty
        <p class="text-gray-500 text-lg">
            {{ $feedType === 'following' ? 'No posts from people you follow yet.' : 'No posts found.' }}
        </p>
    @endforelse
</div>
```

---

### 🔘 Sidebar "Who to Follow" – Follow Button
- **File + Lines:** [`components/who-to-follow.blade.php` L19–L41](file:///c:/laragon/www/musicsocial-main/resources/views/components/who-to-follow.blade.php#L19-L41)
- **Route:** `POST /users/{user}/follow` → [`web.php` L150](file:///c:/laragon/www/musicsocial-main/routes/web.php#L150)
- **Controller:** [`FollowController@toggle`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FollowController.php)

```html
<!-- who-to-follow.blade.php L19–L41 — Follow button with AJAX toggle -->
<form @submit.prevent="
    fetch('{{ route('users.follow', $suggestedUser) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        followed = data.followed;          <!-- Switch button text & color -->
        followersCount = data.followersCount;
    })
">
    <button type="submit"
            x-text="followed ? 'Unfollow' : 'Follow'"
            :class="followed ? 'bg-red-500' : 'bg-blue-600'"
            class="text-white text-xs font-bold py-1.5 px-3 rounded-full ...">
    </button>
</form>
```

---

---

# 🔭 Page 2: Discovery Page (`/discovery`)

**File:** [`resources/views/discovery.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php)  
**Route:** [`routes/web.php` L189](file:///c:/laragon/www/musicsocial-main/routes/web.php#L189)  
**Controller:** [`app/Http/Controllers/DiscoveryController.php`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php)

---

### 🔘 Alpine State Definition (Tab Controller)
- **File + Lines:** [`discovery.blade.php` L2](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L2)

```html
<!-- discovery.blade.php L2 — Page-level Alpine state: controls active tab -->
<div x-data="{ isMusicShareModalOpen: false, activeTab: 'songs' }">
```

---

### 🔘 "How Discovery Works" Banner (Expand/Collapse)
- **File + Lines:** [`discovery.blade.php` L20–L68](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L20-L68)
- **What it does:** An info banner that remembers if the user collapsed it using `localStorage`.

```html
<!-- discovery.blade.php L20 — Collapse toggle state from localStorage -->
<div x-data="{ isCollapsed: localStorage.getItem('discoveryOnboardingCollapsed') === 'true' }">

<!-- discovery.blade.php L60–L68 — The collapse button itself -->
<button @click="isCollapsed = true; localStorage.setItem('discoveryOnboardingCollapsed', 'true')"
        class="absolute top-4 right-4 text-gray-400 ...">
    Collapse
</button>
```

---

### 🔘 "Songs" / "People" Tab Buttons (Mobile)
- **File + Lines:** [`discovery.blade.php` L112–L131](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L112-L131)
- **What it does:** Sets Alpine `activeTab` to `'songs'` or `'people'` to switch which section is visible on mobile.

```html
<!-- discovery.blade.php L112–L131 — Mobile tab nav -->
<nav class="flex ... border-b border-gray-200 dark:border-gray-800 lg:hidden">
    <button @click="activeTab = 'songs'"
            :class="activeTab === 'songs' ? 'border-custom-mid-blue text-gray-900' : 'border-transparent text-gray-400'"
            class="flex-1 py-3 text-sm font-bold ... border-b-2">
        Songs
    </button>
    <button @click="activeTab = 'people'"
            :class="activeTab === 'people' ? 'border-custom-mid-blue text-gray-900' : 'border-transparent text-gray-400'"
            class="flex-1 py-3 text-sm font-bold ... border-b-2">
        People
    </button>
</nav>
```

---

### 🔘 "Export to Spotify" Button (Discovery)
- **File + Lines:** [`discovery.blade.php` L139–L146](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L139-L146)
- **Route:** `POST /export-playlist` → [`web.php` L162](file:///c:/laragon/www/musicsocial-main/routes/web.php#L162)
- **Controller:** [`PlaylistExportController@export`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistExportController.php)

```html
<!-- discovery.blade.php L139–L146 — Export discoveries to Spotify -->
<form action="{{ route('export-playlist') }}" method="POST">
    @csrf
    <input type="hidden" name="source" value="discovery">
    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full ...">
        <!-- Spotify SVG icon -->
        <span>Export to Spotify</span>
    </button>
</form>
```

---

### 🔘 Discovery Card – Spotify Play Button (on album art hover)
- **File + Lines:** [`components/discovery-card.blade.php` L151–L176](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php#L151-L176)
- **What it does:** For Premium users → calls `window.toggleSpotifyPlayer()` to start SDK playback. For free users → plays 30s preview via HTML5 audio.

```html
<!-- discovery-card.blade.php L152–L176 — Play via Spotify SDK -->
<a href="{{ $song->spotify_url }}" target="_blank"
   @click.prevent="
       if(isReady && typeof window.toggleSpotifyPlayer !== 'undefined') {
           window.toggleSpotifyPlayer(
               'spotify:track:{{ $song->spotify_track_id }}',
               {
                   name: '{{ addslashes($song->track_name) }}',
                   artist: '{{ addslashes($song->artist_name) }}',
                   art: '{{ $song->album_art_url }}',
                   previewUrl: '{{ $song->preview_url }}'
               }
           );
       }
   ">
    <!-- Spotify green logo SVG -->
</a>
```

---

### 🔘 Discovery Card – "Pass" (Dislike) / "Like" Buttons
- **File + Lines:** [`components/discovery-card.blade.php` L101–L127](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php#L101-L127) (Alpine function), [`L226–L239`](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php#L226-L239) (buttons)
- **Route:** `POST /song-interactions` → [`web.php` L123](file:///c:/laragon/www/musicsocial-main/routes/web.php#L123)
- **Controller:** [`SongInteractionController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SongInteractionController.php)

```javascript
// discovery-card.blade.php L104–L127 — markInteraction() logs listen/like/dislike
markInteraction(type) {
    this.listened = true;
    fetch('{{ route("song-interactions.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            song_id: {{ $song->id }},
            type: type   // 'like', 'dislike', or 'listen'
        })
    })
    .then(() => {
        this.interacted = true;  // Hides the card with animation
        this.$dispatch('song-interacted');
    });
}
```

```html
<!-- discovery-card.blade.php L227–L239 — The Pass and Like buttons -->
<button @click="markInteraction('dislike')" title="Pass"
        class="flex-1 ... hover:text-red-600 ...">
    <!-- X icon + "PASS" text -->
</button>
<button @click="markInteraction('like')" title="Like"
        class="flex-1 ... hover:text-green-600 ...">
    <!-- Heart icon + "LIKE" text -->
</button>
```

---

### 🔘 Discovery Card – Algorithm Chip & Match Score Bar
- **File + Lines:** [`components/discovery-card.blade.php` L191–L218](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php#L191-L218) (HTML), [`L1–L99`](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php#L1-L99) (PHP logic)
- **What it does:** Displays an animated "Taste Match", "Genre Affinity", "Artist Deep Cut" chip and a percentage score bar. Score from Python ML service.

```php
// discovery-card.blade.php L91–L99 — Score % calculation from Python ML score
if (isset($song->score) && $song->score !== null) {
    // Map ML score (0.05–6.0+) to human-readable 60%–99% range using exponential formula
    $matchScore = (int) round(60 + 39 * (1 - exp(-0.55 * $song->score)));
} else {
    // Seeded random fallback if score is missing
    srand($song->id);
    $matchScore = rand(88, 99);
}
```

```html
<!-- discovery-card.blade.php L196–L218 — The chip + score bar HTML -->
<!-- Algorithm chip -->
<span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $chipColor }}">
    {{ $chipLabel }}  <!-- e.g. "Taste Match", "Genre Affinity", "Social Pick" -->
</span>

<!-- Reason text from Python ML service -->
<p class="text-[11px] text-gray-400 line-clamp-2">{{ $song->reason }}</p>

<!-- Animated score bar (fills on page load with CSS animation) -->
<div class="h-1 bg-gray-100 rounded-full overflow-hidden">
    <div class="h-full rounded-full bg-gradient-to-r {{ $barColor }}"
         x-init="$el.style.width = '0%'; setTimeout(() => $el.style.width = '{{ $matchScore }}%', 120)"
         style="width: {{ $matchScore }}%;">
    </div>
</div>
<span class="text-[10px] font-bold">{{ $matchScore }}%</span>
```

---

### 🔘 Spotify-Style Pill Filter Bar ("All", "Artist Deep Cut", "Sound Profile", "Genre Affinity", etc.)
- **File + Lines:** [`discovery.blade.php` L178–L200](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L178-L200)
- **What it does:** Renders a horizontal scrolling pill bar (like Spotify's Liked Songs) allowing users to filter discovery cards by recommendation signal type in real-time without reloading the page.

```html
<!-- discovery.blade.php L178–L200 — Spotify-Style Pill Filter Bar -->
<div class="mb-5 overflow-x-auto no-scrollbar py-1">
    <div class="flex items-center space-x-2 min-w-max">
        <button @click="selectedChip = 'All'"
                :class="selectedChip === 'All' ? 'bg-custom-mid-blue text-white shadow-md' : 'bg-gray-100 text-gray-600'">
            All
        </button>

        <template x-for="chip in availableChips" :key="chip">
            <button @click="selectedChip = chip"
                    :class="selectedChip === chip ? 'bg-custom-mid-blue text-white shadow-md' : 'bg-gray-100 text-gray-600'">
                <span x-text="chip"></span>
            </button>
        </template>
    </div>
</div>
```

---

### 🔘 "Discover More Songs" Load More Button
- **File + Lines:** [`discovery.blade.php` L215–L227](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L215-L227)
- **What it does:** Expands the rendered batch of recommendations by 12 cards each click (fetching up to 60 personalized songs from backend).

```html
<!-- discovery.blade.php L215–L227 — Discover More Songs Button -->
<div class="mt-8 text-center flex flex-col items-center justify-center space-y-3" x-show="maxRendered < {{ $recommendedSongs->count() }}">
    <button @click="loadMore()" 
            class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold px-8 py-3.5 rounded-full shadow-lg transition-all duration-300">
        <span>Discover More Songs</span>
    </button>
</div>
```

---

### 🔘 Recommendation Diversity Interleaving (Backend Re-ranking)
- **File + Lines:** [`DiscoveryController.php` L74–L98](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php#L74-L98)
- **What it does:** Prevents "Artist Deep Cut" from dominating 100% of the feed by grouping recommendations by signal type (`Artist Deep Cut`, `Sound Profile`, `Genre Affinity`, `Taste Match`, etc.) and round-robin interleaving them into a balanced stream.

```php
// DiscoveryController.php L74–L98 — Interleave recommendations across categories
$grouped = $retrievedSongs->groupBy('chip_label')->map(function ($group) {
    return $group->sortByDesc('score')->values();
});

$diversified = collect();
$maxItemsInGroup = $grouped->map->count()->max() ?? 0;

// Round-robin pick 1 from each category per loop iteration
for ($i = 0; $i < $maxItemsInGroup; $i++) {
    foreach ($grouped as $chipLabel => $songsInGroup) {
        if (isset($songsInGroup[$i])) {
            $diversified->push($songsInGroup[$i]);
        }
    }
}
$recommendedSongs = $diversified;
```

---

### 🔘 "Who to Follow" on Discovery Sidebar
- **File + Lines:** [`discovery.blade.php` L199](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php#L199) (inclusion), [`components/who-to-follow.blade.php` L1–L47](file:///c:/laragon/www/musicsocial-main/resources/views/components/who-to-follow.blade.php#L1-L47) (component)
- **Controller Logic:** [`DiscoveryController.php` L101–L127](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php#L101-L127)

```php
// DiscoveryController.php L103–L127 — Taste Neighbor algorithm
// Tier 1: Users who liked the SAME songs as you (max 3)
$tasteNeighbors = User::where('id', '!=', $user->id)
    ->whereHas('likes', function ($query) use ($likedSongIds) {
        $query->whereIn('song_id', $likedSongIds);  // Co-likes = "same taste"
    })
    ->whereDoesntHave('followers', function ($query) use ($user) {
        $query->where('follower_id', $user->id);    // Exclude already-followed
    })
    ->withCount('followers')
    ->orderByDesc('followers_count')               // Most popular taste-neighbor first
    ->limit(3)
    ->get();

// Tier 2: Random users to fill remaining slots up to 5
$otherUsers = User::where('id', '!=', $user->id)
    ->whereNotIn('id', $tasteNeighbors->pluck('id'))
    ->whereDoesntHave('followers', ...)
    ->inRandomOrder()
    ->limit(5 - $tasteNeighbors->count())         // Fill remaining spots
    ->get();
```

---

---

# 📻 Global Component: Persistent Spotify Web Player

**File:** [`resources/views/components/spotify-web-player.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/components/spotify-web-player.blade.php)  
**Included in:** [`resources/views/layouts/app.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/app.blade.php) (present on ALL pages)  
**Token Route:** `GET /spotify/token` → [`web.php` L156](file:///c:/laragon/www/musicsocial-main/routes/web.php#L156)  
**Token Controller:** [`SpotifyPlayerController@token`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyPlayerController.php)

The global `window.toggleSpotifyPlayer(trackUri, metadata)` function is what every play button on every page calls. It is defined inside `spotify-web-player.blade.php` and wakes up the bottom player bar.

---

---

# 🗺️ Master Route → File → Controller Map (All Pages)

| URL | routes/web.php Line | Controller & Method | View File |
| :--- | :---: | :--- | :--- |
| `GET /` | [L18–L26](file:///c:/laragon/www/musicsocial-main/routes/web.php#L18-L26) | *(closure, no controller)* | [`welcome.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/welcome.blade.php) |
| `GET /dashboard` | [L87–L88](file:///c:/laragon/www/musicsocial-main/routes/web.php#L87-L88) | [`FeedController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php) | [`dashboard.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) |
| `GET /discovery` | [L189](file:///c:/laragon/www/musicsocial-main/routes/web.php#L189) | [`DiscoveryController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php) | [`discovery.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php) |
| `POST /shares` | [L98](file:///c:/laragon/www/musicsocial-main/routes/web.php#L98) | [`ShareController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php) | *(returns JSON + HTML)* |
| `POST /shares/{id}/like` | [L101](file:///c:/laragon/www/musicsocial-main/routes/web.php#L101) | [`LikeController@toggle`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/LikeController.php) | *(returns JSON)* |
| `POST /shares/{id}/bookmark` | [L103](file:///c:/laragon/www/musicsocial-main/routes/web.php#L103) | [`ShareController@toggleBookmark`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php) | *(returns JSON)* |
| `POST /shares/{id}/comments` | [L99](file:///c:/laragon/www/musicsocial-main/routes/web.php#L99) | [`CommentController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php) | *(returns JSON)* |
| `POST /users/{user}/follow` | [L150](file:///c:/laragon/www/musicsocial-main/routes/web.php#L150) | [`FollowController@toggle`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FollowController.php) | *(returns JSON)* |
| `GET /search/tracks` | [L82](file:///c:/laragon/www/musicsocial-main/routes/web.php#L82) | [`SpotifySearchController@search`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifySearchController.php) | *(returns JSON)* |
| `POST /song-interactions` | [L123](file:///c:/laragon/www/musicsocial-main/routes/web.php#L123) | [`SongInteractionController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SongInteractionController.php) | *(returns JSON)* |
| `GET /spotify/token` | [L156](file:///c:/laragon/www/musicsocial-main/routes/web.php#L156) | [`SpotifyPlayerController@token`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyPlayerController.php) | *(returns JSON)* |
| `POST /export-playlist` | [L162](file:///c:/laragon/www/musicsocial-main/routes/web.php#L162) | [`PlaylistExportController@export`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistExportController.php) | *(redirects)* |
| `POST /playlists` | [L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106) | [`PlaylistController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php) | *(redirects)* |
| `POST /admin/retrain` | [L53](file:///c:/laragon/www/musicsocial-main/routes/web.php#L53) | [`AdminController@retrainProcess`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php) | [`admin/retrain.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/admin/retrain.blade.php) |
| `GET /onboarding/genres` | [L36](file:///c:/laragon/www/musicsocial-main/routes/web.php#L36) | [`OnboardingController@genres`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php) | [`onboarding/genres.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php) |
| `GET /users/{name}` | [L146](file:///c:/laragon/www/musicsocial-main/routes/web.php#L146) | [`UserProfileController@show`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php) | [`profile/show.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/show.blade.php) |
| `GET /playlists` | [L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106) | [`PlaylistController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php) | [`playlists/index.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php) |
| `GET /settings` | [L171](file:///c:/laragon/www/musicsocial-main/routes/web.php#L171) | [`SettingsController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SettingsController.php) | [`settings/index.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php) |

---

---

# 👤 Page 3: User Profile Page (`/users/{name}`)

The Profile page is built from **two files working together**: the main shell and a reusable header partial.

| File | Role |
|---|---|
| [`profile/show.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/show.blade.php) | Main page shell — 39 lines total |
| [`profile/partials/header.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php) | Header with banner, avatar, follow button, tabs — 164 lines |
| [`profile/shelf.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php) | Song Shelf sub-page — 273 lines |

**Route:** [`routes/web.php` L146](file:///c:/laragon/www/musicsocial-main/routes/web.php#L146)  
**Controller:** [`UserProfileController@show`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php)

---

### 🔘 Profile Banner – "Edit Cover" Button (own profile only)
- **File + Lines:** [`profile/partials/header.blade.php` L9–L27](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L9-L27)
- **Route:** `PATCH /profile/banner` → [`web.php` L96](file:///c:/laragon/www/musicsocial-main/routes/web.php#L96)
- **Controller:** [`ProfileController@updateBanner`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ProfileController.php)
- **What it does:** Clicking the banner or "Edit Cover" button opens a hidden file input. When a file is selected the form auto-submits.

```html
<!-- header.blade.php L9–L27 — Banner upload form (only shown if you own this profile) -->
@if(auth()->check() && auth()->id() === $user->id)
<form id="banner-upload-form" action="{{ route('profile.banner.update') }}"
      method="POST" enctype="multipart/form-data"
      class="relative group/banner w-full aspect-[3/1] overflow-hidden">
    @csrf
    @method('PATCH')

    <!-- Hidden file input — triggered by button click below -->
    <input type="file" id="cover_photo_input" name="cover_photo" accept="image/*"
           class="hidden"
           onchange="document.getElementById('banner-upload-form').submit()">

    <!-- "Edit Cover" button that appears on hover -->
    <button type="button"
            onclick="document.getElementById('cover_photo_input').click()"
            class="absolute top-4 right-4 bg-black/60 hover:bg-black/80 text-white
                   px-3 py-1.5 rounded-full ... opacity-0 group-hover/banner:opacity-100">
        Edit Cover
    </button>
</form>
@endif
```

---

### 🔘 Profile Avatar – Click to Upload Photo (own profile only)
- **File + Lines:** [`profile/partials/header.blade.php` L39–L54](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L39-L54)
- **Route:** `PATCH /profile/picture` → [`web.php` L95](file:///c:/laragon/www/musicsocial-main/routes/web.php#L95)
- **Controller:** [`ProfileController@updatePicture`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ProfileController.php)

```html
<!-- header.blade.php L40–L53 — Avatar upload form -->
<form id="avatar-upload-form" action="{{ route('profile.picture.update') }}"
      method="POST" enctype="multipart/form-data"
      class="relative group/avatar cursor-pointer shrink-0 z-10">
    @csrf
    @method('PATCH')
    <input type="file" id="profile_picture_input" name="profile_picture"
           accept="image/*" class="hidden"
           onchange="document.getElementById('avatar-upload-form').submit()">

    <!-- Clicking avatar triggers hidden file input -->
    <div onclick="document.getElementById('profile_picture_input').click()"
         class="relative rounded-full">
        <x-user-avatar :user="$user" class="h-24 w-24 border-4 border-white shadow-lg" />
        <!-- Camera icon overlay on hover -->
        <div class="absolute inset-0 bg-black/40 rounded-full ...
                    opacity-0 group-hover/avatar:opacity-100 transition-opacity">
            <!-- Camera SVG icon -->
        </div>
    </div>
</form>
```

---

### 🔘 Follow / Unfollow Button (other people's profiles)
- **File + Lines:** [`profile/partials/header.blade.php` L76–L102](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L76-L102)
- **Route:** `POST /users/{user}/follow` → [`web.php` L150](file:///c:/laragon/www/musicsocial-main/routes/web.php#L150)
- **Controller:** [`FollowController@toggle`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FollowController.php)

```html
<!-- header.blade.php L77–L102 — Follow/Unfollow button with loading spinner -->
<button
    @click="
        loading = true;
        fetch('{{ route('users.follow', $user) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        })
        .then(resp => resp.json())
        .then(data => {
            followed = data.followed;           <!-- true or false -->
            followersCount = data.followersCount;
        })
        .finally(() => loading = false);
    "
    :class="followed
        ? 'bg-gray-200 dark:bg-gray-700 text-gray-800'    <!-- Unfollow styling -->
        : 'bg-custom-mid-blue text-white'"                <!-- Follow styling -->
    class="px-6 py-2 rounded-full font-bold text-sm ...">
    <span x-show="!loading" x-text="followed ? 'Unfollow' : 'Follow'"></span>
    <span x-show="loading" class="w-4 h-4 border-2 ... rounded-full animate-spin"></span>
</button>
```

---

### 🔘 "Edit Profile" Button (your own profile)
- **File + Lines:** [`profile/partials/header.blade.php` L103–L109](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L103-L109)
- **Route:** `GET /settings` → [`web.php` L171](file:///c:/laragon/www/musicsocial-main/routes/web.php#L171)

```html
<!-- header.blade.php L104–L109 — "Edit Profile" navigates to Settings page -->
@else
    <a href="{{ route('settings.index') }}"
       wire:navigate
       class="px-6 py-2 rounded-full font-bold text-sm shadow-sm ... bg-gray-100 hover:bg-gray-200">
        Edit Profile
    </a>
@endif
```

---

### 🔘 Profile Navigation Tabs (Posts / Taste DNA / Song Shelf / Saved)
- **File + Lines:** [`profile/partials/header.blade.php` L124–L161](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L124-L161)
- **Routes:** All standard `GET` links — no AJAX, just page navigation.

```html
<!-- header.blade.php L126–L161 — The 4 profile tab links -->
<nav class="-mb-px flex space-x-8">
    <!-- Tab 1: Posts -->
    <a href="{{ route('profile.show', $user->name) }}" wire:navigate
       class="{{ Route::currentRouteName() === 'profile.show'
                ? 'border-custom-mid-blue text-custom-mid-blue'    <!-- Active: blue underline -->
                : 'border-transparent text-gray-500' }}
              whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
        Posts
    </a>

    <!-- Tab 2: Taste DNA -->
    <a href="{{ route('profile.taste', $user->name) }}" wire:navigate
       class="{{ Route::currentRouteName() === 'profile.taste' ? 'border-custom-mid-blue ...' : '...' }} ...">
        Taste DNA
    </a>

    <!-- Tab 3: Song Shelf -->
    <a href="{{ route('profile.shelf', $user->name) }}" wire:navigate ...>
        Song Shelf
    </a>

    <!-- Tab 4: Saved (only visible to profile owner) -->
    @if(auth()->check() && auth()->id() === $user->id)
    <a href="{{ route('profile.saved', $user->name) }}" wire:navigate ...>
        Saved
    </a>
    @endif
</nav>
```

---

### 🔘 Followers / Following Count Links
- **File + Lines:** [`profile/partials/header.blade.php` L115–L122](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/header.blade.php#L115-L122)
- **Routes:** `GET /users/{user}/followers` and `GET /users/{user}/following` → [`web.php` L179–L180](file:///c:/laragon/www/musicsocial-main/routes/web.php#L179-L180)

```html
<!-- header.blade.php L116–L121 — Follower/Following count links -->
<a href="{{ route('profile.followers', $user) }}" wire:navigate ...>
    <!-- followersCount updates live via Alpine when Follow/Unfollow is clicked -->
    <span class="font-bold" x-text="followersCount">{{ $user->followers()->count() }}</span>
    Followers
</a>
<a href="{{ route('profile.following', $user) }}" wire:navigate ...>
    <span class="font-bold">{{ $user->following()->count() }}</span> Following
</a>
```

---

### 🔘 Song Shelf – "Edit Shelf" / "Done Editing" Toggle Button
- **File + Lines:** [`profile/shelf.blade.php` L32–L40](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L32-L40)
- **What it does:** Toggles `isEditing` Alpine state. When `true`, reorder (←/→) and Remove buttons appear on each song card overlay.

```html
<!-- shelf.blade.php L33–L39 — Edit toggle (owner only) -->
<template x-if="isOwner">
    <button @click="isEditing = !isEditing"
            :class="isEditing ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'">
        <!-- Pencil icon when not editing, Checkmark icon when editing -->
        <span x-text="isEditing ? 'Done Editing' : 'Edit Shelf'"></span>
    </button>
</template>
```

---

### 🔘 Song Shelf – Search & Add Track Button
- **File + Lines:** [`profile/shelf.blade.php` L57–L87](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L57-L87) (UI), [`L185–L215`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L185-L215) (JS function)
- **Route:** `POST /shelf/add` → [`web.php` L174](file:///c:/laragon/www/musicsocial-main/routes/web.php#L174)
- **Controller:** [`UserShelfController@add`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserShelfController.php)

```javascript
// shelf.blade.php L185–L215 — addTrack() sends selected song to backend
async addTrack(track) {
    if (this.tracks.length >= 10) {
        alert('Your shelf is full (max 10 songs).');
        return;
    }
    this.addingTrackId = track.id;
    const response = await fetch('/shelf/add', {  // POST /shelf/add
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ song_id: track.id })
    });
    if (response.ok) {
        this.tracks.push(track);   // Add to local display immediately
        this.searchQuery = '';
        this.searchResults = [];
    }
}
```

---

### 🔘 Song Shelf – "Remove Track" Button
- **File + Lines:** [`profile/shelf.blade.php` L118–L121](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L118-L121) (button), [`L217–L235`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L217-L235) (JS function)
- **Route:** `DELETE /shelf/{songId}` → [`web.php` L175](file:///c:/laragon/www/musicsocial-main/routes/web.php#L175)
- **Controller:** [`UserShelfController@remove`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserShelfController.php)

```javascript
// shelf.blade.php L217–L235 — removeTrack() deletes a shelf song
async removeTrack(trackId) {
    if (!confirm('Remove this track from your shelf?')) return;

    const response = await fetch(`/shelf/${trackId}`, {   // DELETE /shelf/{id}
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
    if (response.ok) {
        this.tracks = this.tracks.filter(t => t.id !== trackId);  // Remove from local display
    }
}
```

---

### 🔘 Song Shelf – ◀ / ▶ Reorder Buttons
- **File + Lines:** [`profile/shelf.blade.php` L104–L116](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L104-L116) (buttons), [`L238–L267`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/shelf.blade.php#L238-L267) (JS function)
- **Route:** `POST /shelf/reorder` → [`web.php` L176](file:///c:/laragon/www/musicsocial-main/routes/web.php#L176)
- **Controller:** [`UserShelfController@reorder`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserShelfController.php)

```javascript
// shelf.blade.php L238–L267 — moveTrack() swaps positions, then saves new order
moveTrack(trackId, direction) {
    const index = this.tracks.findIndex(t => t.id === trackId);
    const newTracks = [...this.tracks];

    if (direction === 'left' && index > 0) {
        // Swap with the item to the LEFT
        [newTracks[index], newTracks[index - 1]] = [newTracks[index - 1], newTracks[index]];
    } else if (direction === 'right' && index < newTracks.length - 1) {
        // Swap with the item to the RIGHT
        [newTracks[index], newTracks[index + 1]] = [newTracks[index + 1], newTracks[index]];
    }

    this.tracks = newTracks;
    this.saveOrder();  // POST /shelf/reorder with new song_ids array
}

async saveOrder() {
    const songIds = this.tracks.map(t => t.id);
    await fetch('/shelf/reorder', {    // POST /shelf/reorder
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '...' },
        body: JSON.stringify({ song_ids: songIds })
    });
}
```

---

---

# 🎵 Page 4: Playlists Page (`/playlists`)

**File:** [`resources/views/playlists/index.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php) — 305 lines  
**Route:** [`routes/web.php` L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106)  
**Controller:** [`PlaylistController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php)

---

### 🔘 Pending Invitations – "Accept" / "Decline" Buttons
- **File + Lines:** [`playlists/index.blade.php` L28–L35](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L28-L35)
- **Routes:** `POST /playlists/{playlist}/accept` and `POST /playlists/{playlist}/decline` → [`web.php` L109–L110](file:///c:/laragon/www/musicsocial-main/routes/web.php#L109-L110)
- **Controller:** [`PlaylistController@acceptInvite`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php) and `declineInvite`

```html
<!-- playlists/index.blade.php L28–L35 — Accept/Decline invite buttons -->
@if($invites->isNotEmpty())
<div class="bg-indigo-900/20 border border-indigo-500/30 rounded-3xl p-6">
    <h3 class="text-xl font-bold text-indigo-400 mb-4">Pending Invitations</h3>
    @foreach($invites as $invite)
        <div class="flex items-center justify-between ...">
            <h4 class="font-bold text-lg">{{ $invite->name }}</h4>
            <div class="flex space-x-2">
                <!-- Accept form -->
                <form action="{{ route('playlists.accept', $invite) }}" method="POST">
                    @csrf
                    <button class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-xl">
                        Accept
                    </button>
                </form>
                <!-- Decline form -->
                <form action="{{ route('playlists.decline', $invite) }}" method="POST">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl">
                        Decline
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endif
```

---

### 🔘 "Import Spotify" Button
- **File + Lines:** [`playlists/index.blade.php` L47–L56](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L47-L56)
- **Route:** `GET /playlists/import/spotify` → [`web.php` L165](file:///c:/laragon/www/musicsocial-main/routes/web.php#L165)
- **Controller:** [`SpotifyImportController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php)
- **What it does:** Navigates to a new page where you can browse your Spotify playlists and selectively import songs into Reso. Shows a pulsing amber dot badge if Spotify is not connected yet.

```html
<!-- playlists/index.blade.php L47–L56 — Import Spotify button -->
<a href="{{ route('playlists.import.index') }}"
   class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg ... flex items-center gap-2 relative">
    <!-- Spotify logo SVG -->
    <span class="hidden sm:inline">Import Spotify</span>

    <!-- Pulsing warning badge (shows if user has NOT connected Spotify yet) -->
    @if(Auth::user()->spotify_token === null)
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
            <span class="animate-ping absolute h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
        </span>
    @endif
</a>
```

---

### 🔘 "New Playlist" Button (opens modal)
- **File + Lines:** [`playlists/index.blade.php` L57–L60](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L57-L60)
- **What it does:** Dispatches an Alpine/Livewire event `open-modal` which opens the "Create Playlist" modal. No page navigation.

```html
<!-- playlists/index.blade.php L57–L60 — New Playlist opens modal -->
<button x-data @click="$dispatch('open-modal', 'create-playlist')"
        class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg ... flex items-center gap-2">
    <!-- Plus icon SVG -->
    <span class="hidden sm:inline">New Playlist</span>
</button>
```

---

### 🔘 "Create Playlist" Modal – Name, Description, Submit
- **File + Lines:** [`playlists/index.blade.php` L249–L275](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L249-L275)
- **Route:** `POST /playlists` → [`web.php` L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106)
- **Controller:** [`PlaylistController@store`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php)

```html
<!-- playlists/index.blade.php L249–L275 — Create Playlist Modal -->
<x-modal name="create-playlist" focusable>
    <form method="post" action="{{ route('playlists.store') }}"
          class="p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-2xl">
        @csrf
        <h2 class="text-2xl font-bold">New Playlist</h2>

        <!-- Playlist Name input -->
        <input type="text" name="name" required
               class="block w-full bg-gray-50 dark:bg-gray-800 border ... rounded-xl py-3 px-4">

        <!-- Optional Description textarea -->
        <textarea name="description" rows="3"
                  class="block w-full bg-gray-50 ..."></textarea>

        <div class="mt-8 flex justify-end gap-3">
            <!-- Cancel dismisses the modal (no server request) -->
            <button type="button" x-on:click="$dispatch('close')">Cancel</button>
            <!-- Submit creates the playlist -->
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white ...">
                Create Playlist
            </button>
        </div>
    </form>
</x-modal>
```

---

### 🔘 Playlist Card – Hover Edit ✏️ and Delete 🗑️ Buttons (owners only)
- **File + Lines:** [`playlists/index.blade.php` L220–L231](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L220-L231) (UI), [`L277–L301`](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php#L277-L301) (delete JS function)
- **Edit Route:** `GET /playlists/{playlist}/edit` → [`web.php` L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106)
- **Delete Route:** `DELETE /playlists/{playlist}` → [`web.php` L106](file:///c:/laragon/www/musicsocial-main/routes/web.php#L106)
- **Controller:** [`PlaylistController@edit`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php) and `destroy`

```html
<!-- playlists/index.blade.php L222–L229 — Edit and Delete buttons (owner hover) -->
@if($userCollab && $userCollab->role === 'owner')
<div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 ...">
    <!-- Edit button navigates to edit page -->
    <a href="{{ route('playlists.edit', $playlist) }}"
       class="bg-white/90 dark:bg-black/90 p-2 rounded-lg ... hover:text-indigo-600">
        <!-- Pencil icon SVG -->
    </a>
    <!-- Delete button calls JS function below -->
    <button type="button"
            onclick="handlePlaylistDelete('{{ $playlist->id }}', '{{ route('playlists.destroy', $playlist) }}')"
            class="bg-white/90 dark:bg-black/90 p-2 rounded-lg ... hover:text-red-600">
        <!-- Trash icon SVG -->
    </button>
</div>
@endif
```

```javascript
// playlists/index.blade.php L277–L301 — handlePlaylistDelete() JS function
const handlePlaylistDelete = async (playlistId, deleteUrl) => {
    if (!confirm("Are you sure you want to permanently delete this playlist?")) return;

    const response = await fetch(deleteUrl, {    // DELETE /playlists/{id}
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    if (result.success) {
        window.location.reload();   // Refresh the page to remove the card
    } else {
        alert("Error deleting playlist: " + result.message);
    }
};
```

---

---

# ⚙️ Page 5: Settings Page (`/settings`)

**File:** [`resources/views/settings/index.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php) — 169 lines  
**Route:** [`routes/web.php` L171](file:///c:/laragon/www/musicsocial-main/routes/web.php#L171)  
**Controller:** [`SettingsController@index`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SettingsController.php)

The Settings page is divided into **5 sections**, each pulled in via `@include` partials:

| Section | Partial File |
|---|---|
| Email Verification | Built inline in `settings/index.blade.php` L36–L86 |
| Connect Social Accounts (Spotify/Google) | [`profile/partials/connect-social-accounts.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/connect-social-accounts.blade.php) |
| Update Profile Info (name, bio, username) | [`profile/partials/update-profile-information-form.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/update-profile-information-form.blade.php) |
| Change Password | [`profile/partials/update-password-form.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/update-password-form.blade.php) |
| Delete Account | [`profile/partials/delete-user-form.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/delete-user-form.blade.php) |

---

### 🔘 Dark Mode Toggle Button (Mobile Settings)
- **File + Lines:** [`settings/index.blade.php` L99–L117](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php#L99-L117)
- **What it does:** Toggles `dark` class on `<html>` and saves preference to `localStorage`. Only visible on mobile (on desktop there's a toggle in the top navbar).

```html
<!-- settings/index.blade.php L99–L117 — Dark mode toggle in Settings (mobile) -->
<div x-data="{
    darkMode: localStorage.getItem('theme') === 'dark',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', this.darkMode);
    }
}">
    <button @click="toggleDarkMode()"
            class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500 ...">
        <!-- Sun icon (shows when dark mode ON — click to go light) -->
        <svg x-show="darkMode" ... class="h-6 w-6 text-yellow-400">...</svg>
        <!-- Moon icon (shows when light mode ON — click to go dark) -->
        <svg x-show="!darkMode" ... class="h-6 w-6 text-gray-700">...</svg>
    </button>
</div>
```

---

### 🔘 "Log Out" Button (Mobile Settings)
- **File + Lines:** [`settings/index.blade.php` L132–L137](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php#L132-L137)
- **Route:** `POST /logout` → [`routes/auth.php`](file:///c:/laragon/www/musicsocial-main/routes/auth.php) (Laravel Breeze)

```html
<!-- settings/index.blade.php L132–L137 — Log Out form -->
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
            class="... bg-gray-100 hover:bg-gray-200 text-red-600 font-bold rounded-lg ... uppercase">
        Log Out
    </button>
</form>
```

---

### 🔘 "Resend Verification Email" Button
- **File + Lines:** [`settings/index.blade.php` L70–L77](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php#L70-L77)
- **Route:** `POST /email/verification-notification` → Laravel Breeze `routes/auth.php`

```html
<!-- settings/index.blade.php L70–L77 — Resend verification email button -->
@else
<!-- Only shown when email is NOT verified yet -->
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 ... text-white ...">
        Resend Verification Email
    </button>
</form>
@endif
```

---

### 🔘 "Connect Spotify" / "Unlink" Button
- **File + Lines:** [`profile/partials/connect-social-accounts.blade.php` L30–L41](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/connect-social-accounts.blade.php#L30-L41)
- **Connect Route:** Dispatches `open-spotify-link-modal` Alpine event (opens OAuth modal).
- **Unlink Route:** `POST /auth/spotify/unlink` → [`web.php` L186](file:///c:/laragon/www/musicsocial-main/routes/web.php#L186)
- **Controller:** [`SocialAuthController@unlink`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php)

```html
<!-- connect-social-accounts.blade.php L30–L41 — Spotify connect/unlink -->
@if(Auth::user()->spotify_id)
    <!-- Already connected: Show "Unlink" (red button) -->
    <form method="POST" action="{{ route('social.unlink', 'spotify') }}" class="inline">
        @csrf
        <button type="submit" class="... bg-red-600 ... text-white uppercase text-xs">
            Unlink
        </button>
    </form>
@else
    <!-- Not connected: Show "Connect Spotify" (green button) -->
    <!-- @click opens a modal that starts the Spotify OAuth flow -->
    <button type="button"
            @click.prevent.stop="$dispatch('open-spotify-link-modal')"
            class="... bg-[#1DB954] hover:bg-[#1ed760] ... text-white uppercase">
        Connect Spotify
    </button>
@endif
```

---

### 🔘 "Connect Google" / "Unlink Google" Button
- **File + Lines:** [`profile/partials/connect-social-accounts.blade.php` L63–L74](file:///c:/laragon/www/musicsocial-main/resources/views/profile/partials/connect-social-accounts.blade.php#L63-L74)
- **Connect Route:** `GET /auth/google` → [`web.php` L184](file:///c:/laragon/www/musicsocial-main/routes/web.php#L184) redirects to Google OAuth.
- **Unlink Route:** `POST /auth/google/unlink` → [`web.php` L186](file:///c:/laragon/www/musicsocial-main/routes/web.php#L186)
- **Controller:** [`SocialAuthController@redirect`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php)

```html
<!-- connect-social-accounts.blade.php L63–L74 — Google connect/unlink -->
@if(Auth::user()->google_id)
    <!-- Already connected: Show Unlink -->
    <form method="POST" action="{{ route('social.unlink', 'google') }}" class="inline">
        @csrf
        <button type="submit" class="... bg-red-600 ... text-white uppercase">
            Unlink
        </button>
    </form>
@else
    <!-- Not connected: Link is a full redirect to Google OAuth -->
    <a href="{{ route('social.redirect', 'google') }}"
       class="... bg-white dark:bg-gray-700 border border-gray-300 ... text-gray-700 ...">
        Connect Google
    </a>
@endif
```

---

---

# 🎬 Page 6: Onboarding / Initiation Page (`/onboarding/genres`)

> This is the **very first page** a new user sees after registering. It is **mandatory** — the user **cannot skip it**. They must pick at least 5 songs before they can enter the app.

**File:** [`resources/views/onboarding/genres.blade.php`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php) — 597 lines  
**Route (load page):** `GET /onboarding/genres` → [`web.php` L36](file:///c:/laragon/www/musicsocial-main/routes/web.php#L36)  
**Route (submit):** `POST /onboarding/genres` → [`web.php` L37](file:///c:/laragon/www/musicsocial-main/routes/web.php#L37)  
**Controller:** [`OnboardingController`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php) — 97 lines

> **No `x-app-layout` wrapper** — this page has its **own standalone HTML** (`<!DOCTYPE html>` from L1) because it runs before the user is fully set up in the app.

---

### 🔘 Full Page Alpine State Definition (`onboardingApp`)
- **File + Lines:** [`genres.blade.php` L384–L591](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L384-L591)
- **What it does:** All interactivity on this page is controlled by a single large Alpine component called `onboardingApp`. It manages the search, the genre tags, the selected track shelf, and the final submit.

```javascript
// genres.blade.php L386–L428 — The Alpine component's data/state
Alpine.data('onboardingApp', () => ({
    searchQuery:    '',      // What user types in the search box
    searchResults:  [],      // Live results from Spotify
    selectedTracks: [],      // Songs the user has picked (shown in shelf)
    isSearching:    false,   // Shows spinner while searching
    isSubmitting:   false,   // Shows spinner on the final submit button
    errorMessage:   '',      // Shows toast error message if something fails

    // Default curated tracks shown before user types anything
    defaultSuggestedTracks: @json($suggestedTracks),
    genreTracks: [],         // Tracks loaded when a genre tag is clicked
    activeTag: null,         // Which genre pill is currently active
    showAllGenres: false,    // Whether niche genres are visible

    // Broad genre pills
    broadGenres: ['Pop', 'Hip-hop', 'R&B', 'Rock', 'Latin', 'Electronic', 'Country'],
    // Niche genre pills (hidden until "More genres" is clicked)
    nicheGenres: ['Jazz', 'Funk', 'Punk', 'Reggae', 'Metal', 'Afrobeats', 'Lo-Fi', 'Math-Rock'],

    // Rotating placeholder text in search box (changes every 3s)
    placeholders: ['Try: local scene artists', 'Try: tracks with <10k plays', ...],
    placeholderIdx: 0,
}))
```

---

### 🔘 Page Headline & Progress Message
- **File + Lines:** [`genres.blade.php` L59–L67](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L59-L67)
- **What it does:** Static headline — no server request. Just HTML text.

```html
<!-- genres.blade.php L59–L67 — Hero headline -->
<div class="text-center pt-2 md:pt-8 ...">
    <h1 class="text-[2rem] md:text-[2.5rem] leading-tight font-black text-slate-900 tracking-tight">
        Let's build your <span class="text-custom-dark-blue">taste profile</span>
    </h1>
    <p class="mt-2.5 text-sm md:text-base text-slate-600 font-medium leading-relaxed">
        Search for a few songs you love.
    </p>
</div>
```

---

### 🔘 Spotify Song Search Box (with rotating placeholder & spinner)
- **File + Lines:** [`genres.blade.php` L79–L93](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L79-L93) (input), [`L515–L534`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L515-L534) (JS function)
- **Route:** `GET /search/tracks?query=...` → [`web.php` L82](file:///c:/laragon/www/musicsocial-main/routes/web.php#L82)
- **Controller:** [`SpotifySearchController@search`](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifySearchController.php)

```html
<!-- genres.blade.php L79–L93 — The search input box -->
<input type="text"
       x-model="searchQuery"
       @input.debounce.400ms="performSearch"    <!-- Waits 400ms after typing to search -->
       :disabled="isSubmitting"
       x-ref="searchInput"
       x-init="$nextTick(() => $refs.searchInput.focus())"   <!-- Auto-focus on page load -->
       :placeholder="placeholders[placeholderIdx]">          <!-- Rotates every 3s -->

<!-- Spinner: shows inside input while searching -->
<div x-show="isSearching" class="absolute inset-y-0 right-0 pr-4 ...">
    <svg class="animate-spin h-4 w-4 text-custom-dark-blue">...</svg>
</div>
```

```javascript
// genres.blade.php L515–L534 — performSearch() runs after user types 3+ characters
async performSearch() {
    if (this.searchQuery.length < 3) {
        this.searchResults = [];
        return;
    }
    this.activeTag = null;    // Deactivate genre tag when typing
    this.isSearching = true;
    try {
        const r = await fetch(`/search/tracks?query=${encodeURIComponent(this.searchQuery)}`);
        if (r.ok) {
            const data = await r.json();
            this.searchResults = Array.isArray(data) ? data : [];
        }
    } catch (e) {
        console.error('Search failed:', e);
    } finally {
        this.isSearching = false;
    }
}
```

---

### 🔘 Genre Tag Pills ("Pop", "Hip-hop", "R&B", etc.)
- **File + Lines:** [`genres.blade.php` L101–L112](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L101-L112) (broad), [`L115–L130`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L115-L130) (niche), [`L481–L513`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L481-L513) (JS function)
- **Route:** Calls `GET /search/tracks?query=genre:pop` (same search endpoint as above)
- **What it does:** Clicking a genre pill calls `selectTag()` which fetches tracks for that genre from Spotify and shows them in the suggestion list. Clicking the same pill again clears it.

```html
<!-- genres.blade.php L101–L112 — Genre tag pill buttons (broad genres) -->
<template x-for="genre in broadGenres" :key="genre">
    <button type="button"
            @click="selectTag(genre)"
            :class="activeTag === genre
                ? 'bg-custom-mid-blue border-custom-mid-blue text-white scale-[1.03]'  <!-- Active: blue filled -->
                : 'bg-custom-periwinkle/10 ... text-custom-dark-blue/80'">              <!-- Inactive: light -->
        <span class="w-1.5 h-1.5 rounded-full block shrink-0"
              :class="activeTag === genre ? 'bg-white' : 'bg-custom-periwinkle'"></span>
        <span x-text="genre"></span>
    </button>
</template>
```

```javascript
// genres.blade.php L481–L513 — selectTag() fetches genre tracks from Spotify
async selectTag(genre) {
    if (this.activeTag === genre) {
        this.activeTag = null;          // Toggle off if already active
        this.genreTracks = [];
        return;
    }
    this.searchQuery = '';              // Clear search box
    this.activeTag = genre;
    this.isLoadingGenre = true;
    try {
        // Strategy 1: search with genre: prefix
        let r = await fetch(`/search/tracks?query=${encodeURIComponent('genre:' + genre.toLowerCase())}`);
        if (r.ok) {
            let data = await r.json();
            if (Array.isArray(data) && data.length > 0) {
                this.genreTracks = data.slice(0, 5);    // Max 5 suggestions
            } else {
                // Strategy 2: fallback to plain genre name search
                let r2 = await fetch(`/search/tracks?query=${encodeURIComponent(genre)}`);
                let data2 = await r2.json();
                this.genreTracks = Array.isArray(data2) ? data2.slice(0, 5) : [];
            }
        }
    } finally {
        this.isLoadingGenre = false;
    }
}
```

---

### 🔘 "More genres" / "Less genres" Expander Button
- **File + Lines:** [`genres.blade.php` L133–L140](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L133-L140)
- **What it does:** Toggles `showAllGenres` Alpine variable. When `true`, the niche genre pills (Jazz, Funk, Punk…) animate into view.

```html
<!-- genres.blade.php L133–L140 — Expand/collapse niche genre pills -->
<button type="button"
        @click="showAllGenres = !showAllGenres"
        class="px-3 py-1.5 rounded-full text-[11px] font-bold ... border border-custom-periwinkle/25">
    <span x-text="showAllGenres ? 'Less genres' : 'More genres'"></span>
    <!-- Chevron rotates 180° when expanded -->
    <svg :class="showAllGenres && 'rotate-180'" class="w-3 h-3 transition-transform">
        <path d="M19 9l-7 7-7-7"/>
    </svg>
</button>
```

---

### 🔘 Song Suggestion List – Click to Select / Deselect a Track
- **File + Lines:** [`genres.blade.php` L175–L194](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L175-L194) (UI), [`L540–L557`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L540-L557) (JS function)
- **What it does:** Clicking any song in the list calls `toggleTrack()`. If already selected → removes it. If not selected and shelf has < 10 songs → adds it. Album art and a checkmark circle animate in.

```html
<!-- genres.blade.php L175–L194 — Suggestion list item (click to select) -->
<template x-for="track in displayedSuggestions" :key="track.id">
    <li @click="toggleTrack(track)"
        class="flex items-center gap-3 py-2.5 cursor-pointer hover:bg-custom-periwinkle/5 ..."
        :class="isSelected(track.id) && 'bg-custom-periwinkle/15'">  <!-- Highlight if selected -->

        <!-- Album art -->
        <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
             class="w-10 h-10 rounded-xl object-cover flex-shrink-0">

        <!-- Track name and artist -->
        <div class="flex-1 min-w-0">
            <div class="text-base font-semibold truncate" x-text="track.name"></div>
            <div class="text-sm text-slate-500 truncate" x-text="getArtistName(track)"></div>
        </div>

        <!-- Circular checkmark — filled when selected, empty ring when not -->
        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center ..."
             :class="isSelected(track.id)
                 ? 'bg-custom-dark-blue border-custom-dark-blue scale-110'
                 : 'border-custom-periwinkle/45 group-hover:border-custom-periwinkle'">
            <!-- Tick icon (only shows when selected) -->
            <svg x-show="isSelected(track.id)" class="w-3 h-3 text-white" fill="currentColor">
                <path d="M16.707 5.293..."/>
            </svg>
        </div>
    </li>
</template>
```

```javascript
// genres.blade.php L540–L557 — toggleTrack() selects or removes a song
toggleTrack(track) {
    if (this.isSelected(track.id)) {
        this.removeTrack(track.id);       // Already selected → remove it
    } else {
        if (this.selectedTracks.length < 10) {
            // Add the track (max 10 allowed)
            const formattedTrack = {
                id: track.id,
                name: track.name,
                artists: track.artists,
                album: track.album
            };
            this.selectedTracks.push(formattedTrack);
        } else {
            this.showError('Maximum 10 tracks — remove one to swap.');
        }
    }
}
```

---

### 🔘 Selected Songs Shelf (Album Art Thumbnails with X Remove Button)
- **File + Lines:** [`genres.blade.php` L254–L289](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L254-L289) (shelf UI), [`L559–L562`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L559-L562) (remove function)
- **What it does:** Shows selected tracks as 48×48px album art thumbnails. Hovering reveals a dark overlay with an ✕ button to remove. A ghost "next slot" badge pulses to invite adding more.

```html
<!-- genres.blade.php L258–L273 — Shelf thumbnails with remove button on hover -->
<template x-for="(track, idx) in selectedTracks" :key="track.id">
    <div class="relative w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 group"
         x-transition:enter-start="opacity-0 scale-50"
         x-transition:enter-end="opacity-100 scale-100">
        <!-- Album art thumbnail -->
        <img :src="track.album?.images[0]?.url" class="w-12 h-12 object-cover">
        <!-- Dark overlay + ✕ remove button (shows on hover) -->
        <button type="button"
                @click.stop="removeTrack(track.id)"
                class="absolute inset-0 bg-black/55 opacity-0 group-hover:opacity-100
                       flex items-center justify-center transition-opacity">
            <svg class="w-3 h-3 text-white">
                <path d="M6 18L18 6M6 6l12 12"/>  <!-- X shape -->
            </svg>
        </button>
    </div>
</template>

<!-- genres.blade.php L277–L287 — Ghost "next slot" pulsing badge -->
<div x-show="selectedTracks.length < 10"
     :class="selectedTracks.length === 0
         ? 'pulse-glow border-custom-periwinkle bg-custom-periwinkle/20'   <!-- Pulses when empty -->
         : (selectedTracks.length >= 5
             ? 'border-emerald-300 bg-emerald-50/50 text-emerald-600'       <!-- Green when ready -->
             : 'border-custom-periwinkle bg-custom-periwinkle/10')"         <!-- Blue while building -->
     class="flex flex-col items-center justify-center w-12 h-12 rounded-xl border-2 border-dashed">
    <!-- Shows "+4", "+3", "+2", "+1", or just "+" when >= 5 -->
    <span x-text="selectedTracks.length < 5 ? '+' + (5 - selectedTracks.length) : '+'"></span>
</div>
```

---

### 🔘 Selection Counter ("3/5 Taste Profile: Building")
- **File + Lines:** [`genres.blade.php` L234–L250](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L234-L250)
- **What it does:** Live counter that reads from `selectedTracks.length`. Changes text and color when the user hits 5 songs.

```html
<!-- genres.blade.php L234–L250 — Live counter and status label -->
<!-- Counter: shows "3/5" (building) or "7/10" (ready, >5 selected) -->
<span class="text-2xl font-black text-custom-dark-blue"
      x-text="selectedTracks.length >= 5
          ? selectedTracks.length + '/10'
          : selectedTracks.length + '/5'">
</span>

<!-- Status label changes at 5 songs -->
<span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest"
      x-text="selectedTracks.length >= 5
          ? 'Taste Profile: Ready'
          : 'Taste Profile: Building'">
</span>

<!-- Countdown text: "Pick 2 more to unlock" / "Ready to continue" -->
<span x-show="selectedTracks.length < 5"
      x-text="'Pick ' + (5 - selectedTracks.length) + ' more to unlock'">
</span>
<span class="text-emerald-600 flex items-center gap-1.5"
      x-show="selectedTracks.length >= 5">
    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
    Ready to continue
</span>
```

---

### 🔘 "Complete Onboarding" Button (locked until 5 songs picked)
- **File + Lines:** [`genres.blade.php` L318–L351](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L318-L351) (button), [`L564–L589`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L564-L589) (JS function)
- **Route:** `POST /onboarding/genres` → [`web.php` L37](file:///c:/laragon/www/musicsocial-main/routes/web.php#L37)
- **Controller:** [`OnboardingController@store` L48–L89](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L48-L89)

```html
<!-- genres.blade.php L318–L351 — Submit button (locked until 5 songs) -->
<button @click="submitShelf"
        :disabled="selectedTracks.length < 5 || isSubmitting"
        :class="selectedTracks.length >= 5
            ? 'bg-gradient-to-r from-custom-dark-blue to-custom-mid-blue text-white cursor-pointer shadow-lg'
            : 'bg-custom-periwinkle/10 text-custom-dark-blue/50 cursor-not-allowed'">

    <!-- Dynamic button label based on how many songs are picked -->
    <template x-if="selectedTracks.length === 0">
        <span>Pick 5 tracks to get started</span>
    </template>
    <template x-if="selectedTracks.length > 0 && selectedTracks.length < 5">
        <span x-text="'Pick ' + (5 - selectedTracks.length) + ' more tracks'"></span>
    </template>
    <template x-if="selectedTracks.length >= 5">
        <span>Complete Onboarding →</span>
    </template>

    <!-- Spinner shown while submitting -->
    <span x-show="isSubmitting" class="flex items-center gap-2">
        <svg class="animate-spin h-4 w-4">...</svg>
        Building your taste profile…
    </span>
</button>
```

```javascript
// genres.blade.php L564–L589 — submitShelf() sends selected songs to backend
async submitShelf() {
    if (this.selectedTracks.length < 5) return;
    this.isSubmitting = true;
    try {
        const r = await fetch('{{ route("onboarding.genres.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            // Sends only the Spotify track IDs to the server
            body: JSON.stringify({ song_ids: this.selectedTracks.map(t => t.id) })
        });
        if (r.ok) {
            const data = await r.json();
            Livewire.navigate(data.redirect);   // → redirects to /dashboard?feed=explore
        } else {
            this.showError('Could not save your shelf. Please try again.');
            this.isSubmitting = false;
        }
    } catch (e) {
        this.showError('Something went wrong. Please try again.');
        this.isSubmitting = false;
    }
}
```

---

### ⚙️ What the Controller Does When You Submit (`OnboardingController@store`)
- **File + Lines:** [`OnboardingController.php` L48–L89](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L48-L89)

```php
// OnboardingController.php L48–L88 — store() processes submitted songs
public function store(Request $request)
{
    // Step 1: Validate — must have 5–10 Spotify IDs
    $validated = $request->validate([
        'song_ids'   => 'required|array|min:5|max:10',
        'song_ids.*' => 'string'
    ]);

    $user = $request->user();

    // Step 2: Clear any previously saved shelf songs
    $user->shelfSongs()->delete();

    // Step 3: For each submitted Spotify ID...
    foreach ($validated['song_ids'] as $index => $spotifyId) {
        $trackData = $this->spotifyService->getTrack($spotifyId);
        $song = $trackData['song'];

        // Save to shelf (position = order user added them)
        UserShelfSong::create([
            'user_id'  => $user->id,
            'song_id'  => $spotifyId,
            'position' => $index,
        ]);

        // Also log as a 'like' interaction → feeds into the ML recommender
        SongInteraction::updateOrCreate(
            ['user_id' => $user->id, 'song_id' => $song->id],
            ['type' => 'like', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    // Step 4: Mark user as onboarded so middleware won't redirect them here again
    $user->update(['is_onboarded' => true]);

    // Step 5: Redirect to the Explore feed (personalized from their picks)
    return response()->json([
        'message'  => 'Shelf curated successfully.',
        'redirect' => route('dashboard', ['feed' => 'explore'])
    ]);
}
```

> **Why this matters for your examiner:** The `is_onboarded = true` flag is checked by a **middleware** on every page. If false, the user is redirected back to this page. This enforces the minimum data requirement for the ML recommender (at least 5 songs needed for TF-IDF profiling).

---

### 🔘 Error Toast Notification
- **File + Lines:** [`genres.blade.php` L366–L380](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L366-L380), [`L438–L441`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L438-L441) (JS function)

```html
<!-- genres.blade.php L366–L380 — Error toast (slides in from top, auto-dismisses after 4s) -->
<div x-show="errorMessage"
     x-transition:enter-start="opacity-0 -translate-y-3"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed top-14 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm px-4">
    <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3">
        <!-- Red warning icon + error message text -->
        <span x-text="errorMessage"></span>
    </div>
</div>
```

```javascript
// genres.blade.php L438–L441 — showError() auto-dismisses after 4 seconds
showError(msg) {
    this.errorMessage = msg;
    setTimeout(() => { this.errorMessage = ''; }, 4000);  // Auto-dismiss
}
```

---

### 🔘 Default Curated Songs (loaded by Controller before user types)
- **File + Lines:** [`OnboardingController.php` L20–L45](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L20-L45), [`genres.blade.php` L395`](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php#L395)
- **What it does:** 4 diverse "editor's picks" are fetched from Spotify and **cached for 24 hours** so every new user sees them immediately without waiting.

```php
// OnboardingController.php L20–L45 — genres() loads 4 diverse starter tracks (cached 24h)
public function genres()
{
    $suggestedTracks = Cache::remember('onboarding_diverse_suggested_tracks', 60 * 60 * 24, function () {
        $ids = [
            '4D7t7g2jsYii9v173y506G', // Pop: Harry Styles - As It Was
            '5uCaxm20t3865UpVJb0GgC', // Rock: Nirvana - Smells Like Teen Spirit
            '7y620WfXhU1g0Z42L6zG2k', // Jazz: Frank Sinatra - Fly Me To The Moon
            '5GDAWNs8t162gJV61PWqyW', // Afrobeats: Burna Boy - Last Last
        ];
        $tracks = [];
        foreach ($ids as $id) {
            $track = $this->spotifyService->getRawTrack($id);
            if ($track && !isset($track['error'])) {
                $tracks[] = $track;
            }
        }
        return $tracks;   // PHP array → passed to @json($suggestedTracks) in the view
    });

    return view('onboarding.genres', compact('suggestedTracks'));
}
```

```javascript
// genres.blade.php L395 — PHP data is embedded into JS at page render time
defaultSuggestedTracks: @json($suggestedTracks),   // PHP → JSON in the page HTML
```

---

## 💡 Quick Learning Tip

When you find any button confusing, just look at:
1. **What HTML file it's in** → `resources/views/`
2. **What URL it calls** → `routes/web.php`
3. **What PHP function handles it** → `app/Http/Controllers/`
4. **What database table it touches** → `app/Models/`
