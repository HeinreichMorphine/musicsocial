@extends('layouts.admin')

@section('title', 'Moderate Content')

@push('styles')
<style>
    .mod-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 900px) { .mod-grid { grid-template-columns: 1fr; } }

    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04); overflow: hidden;
    }
    .panel-head {
        padding: .875rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap;
    }
    .panel-head h4 { font-size: .88rem; font-weight: 700; color: #0f172a; margin: 0; }

    .search-mini { display: flex; gap: .5rem; flex: 1; min-width: 160px; max-width: 280px; }
    .search-mini input {
        flex: 1; padding: .4rem .75rem; border: 1.5px solid #e2e8f0; border-radius: 7px;
        font-size: .78rem; outline: none; color: #374151;
        transition: border-color .2s;
    }
    .search-mini input:focus { border-color: #1d4ed8; }
    .search-mini button {
        padding: .4rem .75rem; background: #1d4ed8; color: #fff;
        border: none; border-radius: 7px; font-size: .78rem; cursor: pointer;
    }

    .mod-table { width: 100%; border-collapse: collapse; }
    .mod-table thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .mod-table th {
        padding: .55rem .875rem; text-align: left;
        font-size: .68rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .mod-table td {
        padding: .65rem .875rem; font-size: .8rem; color: #374151;
        border-bottom: 1px solid #f8fafc; vertical-align: middle;
    }
    .mod-table tbody tr:last-child td { border-bottom: none; }
    .mod-table tbody tr:hover { background: #fafbff; }

    .author-chip { font-weight: 600; color: #0f172a; font-size: .8rem; }
    .time-chip  { font-size: .7rem; color: #94a3b8; display: block; margin-top: 1px; }
    .content-preview { color: #64748b; font-size: .77rem; max-width: 220px; }
    .likes-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #fff7ed; color: #ea580c;
        font-size: .7rem; font-weight: 700; padding: .15rem .45rem; border-radius: 20px;
    }
    .btn-del-sm {
        padding: .28rem .65rem; background: #fef2f2; color: #dc2626;
        border: none; border-radius: 6px; font-size: .73rem; font-weight: 600;
        cursor: pointer; transition: opacity .15s; white-space: nowrap;
    }
    .btn-del-sm:hover { opacity: .8; }

    .pagination-wrap { padding: .75rem .875rem; border-top: 1px solid #f1f5f9; }
</style>
@endpush

@section('content')
<div class="mod-grid">

    {{-- ── Shares Panel ─────────────────────────────────────── --}}
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
                            <span class="author-chip">{{ $share->user->name ?? 'Unknown' }}</span>
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

    {{-- ── Comments Panel ───────────────────────────────────── --}}
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
                            <span class="author-chip">{{ $comment->user->name ?? 'Unknown' }}</span>
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
@endsection
