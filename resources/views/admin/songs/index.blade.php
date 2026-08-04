@extends('layouts.admin')

@section('title', 'Manage Songs')

@push('styles')
<style>
    .search-bar-row {
        display: flex; gap: 1rem; align-items: center;
        margin-bottom: 1.5rem; flex-wrap: wrap;
    }
    .search-input-wrap { position: relative; flex: 1; min-width: 220px; }
    .search-input-wrap i {
        position: absolute; left: 1rem; top: 50%;
        transform: translateY(-50%); color: #94a3b8; font-size: 0.95rem;
    }
    .search-input {
        width: 100%; padding: .75rem 1rem .75rem 2.6rem !important;
        border: 1.5px solid #e2e8f0; border-radius: 8px !important;
        font-size: 0.95rem; color: #0f172a; background: #fff; outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .search-input:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.07); }
    .sort-select {
        padding: .75rem 1.5rem .75rem 1rem !important;
        border: 1.5px solid #e2e8f0; border-radius: 8px !important;
        font-size: 0.95rem; color: #0f172a; background: #fff; outline: none;
        width: auto !important; min-width: 200px;
        transition: border-color .2s, box-shadow .2s;
    }
    .sort-select:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.07); }
    .btn-search {
        padding: .75rem 1.5rem; background: #1d4ed8; color: #fff; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .btn-search:hover { background: #1e40af; }
    .btn-clear {
        padding: .75rem 1.25rem; background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0;
        border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center; gap: .35rem;
        transition: background .15s;
    }
    .btn-clear:hover { background: #e2e8f0; }

    .btn-add {
        padding: .75rem 1.5rem; background: #16a34a; color: #fff; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center; gap: .35rem;
        transition: background .15s;
    }
    .btn-add:hover { background: #15803d; color: #fff; }

    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04); overflow: hidden;
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }

    .songs-table { width: 100%; border-collapse: collapse; }
    .songs-table thead tr {
        background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    }
    .songs-table th {
        padding: 1rem 1.25rem; text-align: left;
        font-size: 0.85rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .songs-table td {
        padding: 1rem 1.25rem; font-size: 0.95rem; color: #374151;
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .songs-table tbody tr:last-child td { border-bottom: none; }
    .songs-table tbody tr:hover { background: #fafbff; }

    .av-img { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; }
    
    .action-btn {
        padding: .45rem .85rem; border-radius: 6px; font-size: 0.85rem;
        font-weight: 600; border: none; cursor: pointer; display: inline-flex;
        align-items: center; gap: .4rem; transition: opacity .15s;
    }
    .action-btn:hover { opacity: .85; }
    .btn-edit { background: #eff6ff; color: #1d4ed8; text-decoration: none; }
    .btn-edit:hover { color: #1d4ed8; text-decoration: none; }
    .btn-del  { background: #fef2f2; color: #dc2626; }
    .btn-refetch { background: #f0fdf4; color: #16a34a; }
    .btn-refetch:hover { background: #dcfce7; color: #15803d; }
    .btn-refetch:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .results-info { font-size: 0.9rem; color: #94a3b8; margin-bottom: 1rem; }
    
    .genres-list { font-size: 0.8rem; color: #64748b; }
    
    /* Sync API button styles (hover reveal on blank-tag rows) */
    .sync-group { display: flex; align-items: center; gap: 8px; }
    .sync-btn {
        opacity: 0; background: #e0e7ff; color: #4f46e5; border: none; padding: 4px 8px;
        border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: opacity 0.2s, background 0.2s;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .sync-btn:hover { background: #c7d2fe; }
    .sync-group:hover .sync-btn { opacity: 1; }
    .sync-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    @media (max-width: 640px) {
        .action-btn { padding: .35rem .65rem; font-size: 0.78rem; }
        .av-img { width: 34px; height: 34px; }
        .songs-table td { padding: 0.65rem 0.4rem !important; }
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="panel-card">
    <div class="panel-head">
        <div style="display:flex;align-items:center;gap:1rem;">
            <h4><i class="fa fa-music" style="color:#1d4ed8;margin-right:6px;"></i> Manage Songs</h4>
            <small>{{ $songs->total() }} total songs</small>
        </div>
        <a href="{{ route('admin.songs.create') }}" class="btn-add"><i class="fa fa-plus"></i> Add Song</a>
    </div>
    <div style="padding: 1rem 1.25rem;">

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('admin.songs') }}" class="search-bar-row">
            <div class="search-input-wrap">
                <i class="fa fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by track name, artist, or genre..." class="search-input">
            </div>
            
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="latest" {{ (request('sort') ?? 'latest') == 'latest' ? 'selected' : '' }}>Sort By: Newest First</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Sort By: Oldest First</option>
                <option value="untagged" {{ request('sort') == 'untagged' ? 'selected' : '' }}>Filter: Untagged Songs Only</option>
                <option value="shares" {{ request('sort') == 'shares' ? 'selected' : '' }}>Sort By: Most Shares</option>
            </select>
            
            <button type="submit" class="btn-search"><i class="fa fa-search"></i> Search</button>
            @if(request('search'))
                <a href="{{ route('admin.songs') }}" class="btn-clear"><i class="fa fa-times"></i> Clear</a>
            @endif
        </form>

        <p class="results-info">Showing {{ $songs->total() }} total {{ \Str::plural('song', $songs->total()) }}.</p>

        {{-- Desktop Table View --}}
        <div class="desktop-only-table" style="overflow-x:auto;">
            <table class="songs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Track</th>
                        <th>Artist</th>
                        <th>Genres</th>
                        <th>Shares</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($songs as $song)
                    <tr>
                        <td style="color:#94a3b8;font-size:.72rem;">#{{ $song->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <img class="av-img" src="{{ $song->album_art_url }}" alt="Album Art">
                                <span style="font-weight:600;color:#0f172a;">{{ $song->track_name }}</span>
                            </div>
                        </td>
                        <td>{{ $song->artist_name }}</td>
                        <td id="genres-cell-{{ $song->id }}">
                            @php
                                $genres = $song->genres ? json_decode($song->genres, true) : [];
                            @endphp
                            @if(!empty($genres))
                                <span class="genres-list">{{ implode(', ', array_slice($genres, 0, 3)) }}{{ count($genres) > 3 ? '...' : '' }}</span>
                            @else
                                <div class="sync-group">
                                    <span style="color:#94a3b8; font-style: italic; font-size: 0.85rem;">No tags</span>
                                    <button 
                                        onclick="handleRefetch({{ $song->id }}, this)"
                                        class="sync-btn"
                                        title="Bypass cache and force re-scan all API endpoints"
                                    >
                                        <i class="fa fa-refresh"></i> Sync APIs
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td style="text-align:center;font-weight:600;">{{ ($song->shares_count ?? 0) + ($song->comments_count ?? 0) }}</td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                <a href="{{ route('admin.songs.edit', $song->id) }}" class="action-btn btn-edit">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                @if($song->spotify_track_id)
                                <button
                                    id="refetch-btn-{{ $song->id }}"
                                    onclick="handleRefetch({{ $song->id }}, this)"
                                    class="action-btn btn-refetch"
                                    title="Re-scan Spotify, MusicBrainz, Discogs & YouTube for genre tags"
                                >
                                    <i class="fa fa-refresh"></i> Refetch
                                </button>
                                @endif
                                <form action="{{ route('admin.songs.delete', $song->id) }}" method="POST"
                                      onsubmit="return confirm('Delete {{ addslashes($song->track_name) }}? This cannot be undone.');" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">
                            @if($search) No songs match "{{ $search }}" @else No songs found. @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View --}}
        <div class="mobile-only-card-list">
            @forelse($songs as $song)
            <div class="mob-card">
                <div class="mob-card-head">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
                        <img style="width:46px;height:46px;border-radius:10px;object-fit:cover;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.1);"
                             src="{{ $song->album_art_url }}" alt="Cover">
                        <div style="min-width:0;flex:1;">
                            <div class="mob-card-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $song->track_name }}</div>
                            <div class="mob-card-sub" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $song->artist_name }}</div>
                        </div>
                    </div>
                    <span style="font-size:0.72rem;color:#94a3b8;font-weight:700;flex-shrink:0;">#{{ $song->id }}</span>
                </div>

                <div class="mob-card-meta">
                    @php
                        $mobGenres = $song->genres ? json_decode($song->genres, true) : [];
                    @endphp
                    <div style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <div style="min-width:0;flex:1;">
                            @if(!empty($mobGenres))
                                <span class="genres-list"><i class="fa fa-tags" style="color:#7c3aed;margin-right:4px;"></i> {{ implode(', ', array_slice($mobGenres, 0, 3)) }}</span>
                            @else
                                <span style="color:#94a3b8;font-style:italic;font-size:0.8rem;"><i class="fa fa-tag" style="margin-right:4px;"></i> No genre tags</span>
                            @endif
                        </div>
                        <span style="font-weight:700;color:#1d4ed8;font-size:0.8rem;white-space:nowrap;">
                            <i class="fa fa-share-alt"></i> {{ ($song->shares_count ?? 0) + ($song->comments_count ?? 0) }}
                        </span>
                    </div>
                </div>

                <div class="mob-card-actions">
                    <a href="{{ route('admin.songs.edit', $song->id) }}" class="action-btn btn-edit">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    @if($song->spotify_track_id)
                    <button onclick="handleRefetch({{ $song->id }}, this)" class="action-btn btn-refetch">
                        <i class="fa fa-refresh"></i> Refetch
                    </button>
                    @endif
                    <form action="{{ route('admin.songs.delete', $song->id) }}" method="POST"
                          onsubmit="return confirm('Delete {{ addslashes($song->track_name) }}? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn btn-del">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:2rem;color:#94a3b8;background:#fff;border-radius:12px;border:1px solid #e2e8f0;">
                @if($search) No songs match "{{ $search }}" @else No songs found. @endif
            </div>
            @endforelse
        </div>

        <div style="margin-top:1rem;">
            {{ $songs->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleRefetch(songId, btn) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';
    btn.disabled = true;

    fetch(`/admin/songs/${songId}/refetch-genres`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the genres cell
            const cell = document.getElementById(`genres-cell-${songId}`);
            if (data.genres && data.genres.length > 0) {
                const displayGenres = data.genres.slice(0, 3).join(', ') + (data.genres.length > 3 ? '...' : '');
                cell.innerHTML = `<span class="genres-list">${displayGenres}</span>`;
                btn.innerHTML = '<i class="fa fa-check"></i> Done';
                btn.style.background = '#dcfce7';
            } else {
                cell.innerHTML = `<div class="sync-group"><span style="color:#94a3b8;font-style:italic;font-size:.85rem;">No tags found</span></div>`;
                btn.innerHTML = originalHtml;
            }
            setTimeout(() => {
                btn.innerHTML = '<i class="fa fa-refresh"></i> Refetch';
                btn.style.background = '';
                btn.disabled = false;
            }, 2500);
        } else {
            alert(data.message || 'Failed to sync tags.');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    })
    .catch(error => {
        alert('An error occurred while syncing.');
        console.error(error);
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
</script>
@endpush
