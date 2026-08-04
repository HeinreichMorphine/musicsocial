@extends('layouts.admin')

@section('title', 'Edit Song')

@push('styles')
<style>
    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04); overflow: hidden; margin-bottom: 24px;
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
    
    .form-group label {
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin-bottom: 6px !important;
        display: block;
    }
    .form-control {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.95rem !important;
        color: #0f172a !important;
        background: #f8fafc !important;
        box-shadow: none !important;
        transition: all 0.2s;
    }
    .form-control:focus {
        background: #fff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    .help-block {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 6px;
    }
    
    .btn-save {
        padding: 0.85rem 2rem; background: #16a34a; color: #fff; border: none;
        border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;
        transition: background .15s; width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 8px;
    }
    .btn-save:hover { background: #15803d; }
    
    .btn-cancel {
        padding: 0.85rem 2rem; background: #f1f5f9; color: #475569; border: none;
        border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;
        transition: background .15s; width: 100%; display: inline-flex; justify-content: center; align-items: center; text-decoration: none;
    }
    .btn-cancel:hover { background: #e2e8f0; color: #0f172a; text-decoration: none; }

    /* Alpine Tag Input Styles */
    .tag-container {
        display: flex; flex-wrap: wrap; gap: 6px; padding: 6px;
        border: 1.5px solid #e2e8f0; border-radius: 8px; background: #f8fafc;
        min-height: 48px;
    }
    .tag-container:focus-within {
        background: #fff; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .tag-badge {
        background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 6px;
        font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
    }
    .tag-badge button {
        background: none; border: none; color: #60a5fa; cursor: pointer; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .tag-badge button:hover { color: #1d4ed8; }
    .tag-input {
        flex: 1; min-width: 120px; border: none; background: transparent; outline: none;
        padding: 4px; font-size: 0.95rem; color: #0f172a;
    }

    @media (max-width: 640px) {
        .btn-save, .btn-cancel { padding: 0.75rem 1rem; font-size: 0.95rem; }
    }
</style>
<!-- Include Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<form action="{{ route('admin.songs.update', $song->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Left Column: Core Metadata -->
        <div class="col-md-8">
            <div class="panel-card">
                <div class="panel-head">
                    <h4><i class="fa fa-info-circle" style="color:#3b82f6;margin-right:6px;"></i> Edit Core Metadata</h4>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="form-group mb-4">
                        <label for="track_name">Song Title <span class="text-danger">*</span></label>
                        <input type="text" id="track_name" name="track_name" class="form-control" value="{{ old('track_name', $song->track_name) }}" required>
                        @error('track_name') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="artist_name">Artist(s) <span class="text-danger">*</span></label>
                        <input type="text" id="artist_name" name="artist_name" class="form-control" value="{{ old('artist_name', $song->artist_name) }}" required>
                        <p class="help-block">If multiple, separate with commas.</p>
                        @error('artist_name') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="release_date">Release Date</label>
                        <input type="date" id="release_date" name="release_date" class="form-control" value="{{ old('release_date', $song->release_date ? \Carbon\Carbon::parse($song->release_date)->format('Y-m-d') : '') }}">
                        @error('release_date') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Alpine.js Tag Input for Genres -->
                    @php
                        // Format genres back into comma-separated string for old() or initial Alpine state
                        $initialGenres = old('genres', '');
                        if (!$initialGenres && $song->genres) {
                            $decoded = json_decode($song->genres, true);
                            if (is_array($decoded)) {
                                $initialGenres = implode(', ', $decoded);
                            }
                        }
                    @endphp
                    <div class="form-group mb-4" x-data="tagInput('{{ addslashes($initialGenres) }}')">
                        <label for="genres">Genres / Tags</label>
                        
                        <input type="hidden" name="genres" :value="tags.join(', ')">
                        
                        <div class="tag-container" @click="$refs.input.focus()">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span class="tag-badge">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(index)">&times;</button>
                                </span>
                            </template>
                            
                            <input 
                                type="text" 
                                class="tag-input" 
                                x-ref="input"
                                x-model="newTag" 
                                @keydown.enter.prevent="addTag()"
                                @keydown.comma.prevent="addTag()"
                                @keydown.backspace="removeLastTag()"
                                placeholder="Type a genre and press Enter..."
                            >
                        </div>
                        <p class="help-block">Press Enter or Comma to add a tag.</p>
                        @error('genres') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Integrations & Media -->
        <div class="col-md-4">
            <div class="panel-card">
                <div class="panel-head">
                    <h4><i class="fa fa-link" style="color:#10b981;margin-right:6px;"></i> Integrations & Media</h4>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="form-group mb-4 text-center">
                        <img src="{{ $song->album_art_url }}" alt="Cover" style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 1rem;">
                    </div>

                    <div class="form-group mb-4">
                        <label for="album_art_url">Cover Art URL</label>
                        <input type="url" id="album_art_url" name="album_art_url" class="form-control" value="{{ old('album_art_url', $song->getRawOriginal('album_art_url')) }}">
                        @error('album_art_url') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="spotify_track_id">Spotify Track ID or URL</label>
                        <input type="text" id="spotify_track_id" name="spotify_track_id" class="form-control" value="{{ old('spotify_track_id', $song->spotify_track_id) }}">
                        @error('spotify_track_id') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="youtube_url">YouTube Video URL</label>
                        <input type="url" id="youtube_url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $song->youtube_url) }}">
                        @error('youtube_url') <span class="text-danger text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="panel-card">
                <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 12px;">
                    <button type="submit" class="btn-save"><i class="fa fa-save"></i> Save Changes</button>
                    <a href="{{ route('admin.songs') }}" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tagInput', (initialTags) => ({
            tags: initialTags ? initialTags.split(',').map(t => t.trim()).filter(t => t.length > 0) : [],
            newTag: '',
            addTag() {
                const tag = this.newTag.trim();
                if (tag && !this.tags.includes(tag)) {
                    this.tags.push(tag);
                }
                this.newTag = '';
            },
            removeTag(index) {
                this.tags.splice(index, 1);
            },
            removeLastTag() {
                if (this.newTag === '' && this.tags.length > 0) {
                    this.tags.pop();
                }
            }
        }));
    });
</script>
@endpush
@endsection
