@extends('layouts.admin')

@section('title', 'Moderate Content')

@push('styles')
<style>


    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04); overflow: hidden;
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }

    .search-mini { display: flex; gap: .65rem; flex: 1; min-width: 160px; max-width: 320px; }
    .search-mini input {
        flex: 1; padding: .55rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.9rem; outline: none; color: #374151;
        transition: border-color .2s;
    }
    .search-mini input:focus { border-color: #1d4ed8; }
    .search-mini button {
        padding: .55rem 1rem; background: #1d4ed8; color: #fff;
        border: none; border-radius: 8px; font-size: 0.9rem; cursor: pointer;
    }

    .mod-table { width: 100%; border-collapse: collapse; }
    .mod-table thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .mod-table th {
        padding: 0.9rem 1.1rem; text-align: left;
        font-size: 0.82rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .mod-table td {
        padding: 0.9rem 1.1rem; font-size: 0.95rem; color: #374151;
        border-bottom: 1px solid #f8fafc; vertical-align: middle;
    }
    .mod-table tbody tr:last-child td { border-bottom: none; }
    .mod-table tbody tr:hover { background: #fafbff; }

    .author-chip { font-weight: 600; color: #0f172a; font-size: 0.95rem; }
    .time-chip  { font-size: 0.82rem; color: #94a3b8; display: block; margin-top: 2px; }
    .content-preview { color: #64748b; font-size: 0.92rem; max-width: 250px; }
    .likes-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #fff7ed; color: #ea580c;
        font-size: 0.82rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px;
    }
    .btn-del-sm {
        padding: .4rem .8rem; background: #fef2f2; color: #dc2626;
        border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; transition: opacity .15s; white-space: nowrap;
    }
    .btn-del-sm:hover { opacity: .8; }

    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; }
</style>
@endpush

@section('content')
<div class="row">

    {{-- ── Shares Panel ─────────────────────────────────────── --}}
    <div class="col-md-6 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-share-alt" style="color:#16a34a;margin-right:5px;"></i> Shares
                <span style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-left:4px;">({{ $shares->total() }})</span>
            </h4>
            <form method="GET" action="{{ route('admin.moderation') }}" class="search-mini">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Filter by user…">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Song / Caption</th>
                        <th>Likes</th>
                        <th>Posted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shares as $share)
                    <tr>
                        <td>
                            <span class="author-chip">{{ $share->user?->name ?? 'Unknown' }}</span>
                        </td>
                        <td>
                            <div class="content-preview">
                                @if($share->song)
                                    <strong>{{ Str::limit($share->song->track_name, 30) }}</strong><br>
                                    <span style="color:#94a3b8;">{{ $share->song->artist_name }}</span>
                                @else
                                    {{ Str::limit($share->caption ?? '—', 45) }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="likes-pill"><i class="fa fa-heart"></i> {{ $share->likes_count }}</span>
                        </td>
                        <td>
                            <span class="time-chip">{{ $share->created_at->format('d M Y') }}</span>
                            <span class="time-chip">{{ $share->created_at->diffForHumans() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.shares.delete', $share->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this share and all related data?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-sm"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:1.5rem;color:#94a3b8;">No shares found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $shares->links() }}</div>
    </div>
    </div>

    {{-- ── Comments Panel ───────────────────────────────────── --}}
    <div class="col-md-6 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-comments" style="color:#7c3aed;margin-right:5px;"></i> Comments
                <span style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-left:4px;">({{ $comments->total() }})</span>
            </h4>
            <form method="GET" action="{{ route('admin.moderation') }}" class="search-mini">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Filter by user or text…">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Posted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                    <tr>
                        <td>
                            <span class="author-chip">{{ $comment->user?->name ?? 'Unknown' }}</span>
                        </td>
                        <td>
                            <div class="content-preview">{{ Str::limit($comment->body ?? '—', 70) }}</div>
                        </td>
                        <td>
                            <span class="time-chip">{{ $comment->created_at->format('d M Y') }}</span>
                            <span class="time-chip">{{ $comment->created_at->diffForHumans() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this comment?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-sm"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:1.5rem;color:#94a3b8;">No comments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $comments->links() }}</div>
    </div>
    </div>
    {{-- ── Playlists Panel ───────────────────────────────────── --}}
    <div class="col-md-12 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-list" style="color:#ef4444;margin-right:5px;"></i> Playlists
                <span style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-left:4px;">({{ $playlists->total() }})</span>
            </h4>
            <form method="GET" action="{{ route('admin.moderation') }}" class="search-mini">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Filter by user or name…">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>Creator</th>
                        <th>Playlist Name</th>
                        <th>Songs</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($playlists as $playlist)
                    <tr>
                        <td>
                            <span class="author-chip">{{ $playlist->creator?->user?->name ?? 'Unknown' }}</span>
                        </td>
                        <td>
                            <div class="content-preview">{{ Str::limit($playlist->name ?? '—', 70) }}</div>
                        </td>
                        <td>
                            <span class="likes-pill" style="color:#2563eb; background:#eff6ff;"><i class="fa fa-music"></i> {{ $playlist->songs_count }}</span>
                        </td>
                        <td>
                            <span class="time-chip">{{ $playlist->created_at->format('d M Y') }}</span>
                            <span class="time-chip">{{ $playlist->created_at->diffForHumans() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.playlists.delete', $playlist->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this playlist and all related data?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-sm"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:1.5rem;color:#94a3b8;">No playlists found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $playlists->links() }}</div>
    </div>
    </div>

</div>
@endsection
