@extends('layouts.admin')

@section('title', 'Manage Users')

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

    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04); overflow: hidden;
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }

    .users-table { width: 100%; border-collapse: collapse; }
    .users-table thead tr {
        background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    }
    .users-table th {
        padding: 1rem 1.25rem; text-align: left;
        font-size: 0.85rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .users-table td {
        padding: 1rem 1.25rem; font-size: 0.95rem; color: #374151;
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .users-table tbody tr:last-child td { border-bottom: none; }
    .users-table tbody tr:hover { background: #fafbff; }

    .av-wrap {
        width: 42px; height: 42px; border-radius: 8px; overflow: hidden; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .av-img { width: 100%; height: 100%; object-fit: cover; }
    .av-init {
        width: 42px; height: 42px; border-radius: 8px; background: #eff6ff; color: #1d4ed8;
        font-weight: 700; font-size: 0.9rem;
        display: inline-flex; align-items: center; justify-content: center;
    }

    .badge-active { background: #f0fdf4; color: #16a34a; padding: .25rem .65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
    .badge-banned { background: #fef2f2; color: #dc2626; padding: .25rem .65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }

    .action-btn {
        padding: .45rem .85rem; border-radius: 6px; font-size: 0.85rem;
        font-weight: 600; border: none; cursor: pointer; display: inline-flex;
        align-items: center; gap: .4rem; transition: opacity .15s;
    }
    .action-btn:hover { opacity: .85; }
    .btn-del  { background: #fef2f2; color: #dc2626; }
    .btn-ban  { background: #fff7ed; color: #ea580c; }
    .btn-unban{ background: #f0fdf4; color: #16a34a; }

    .results-info { font-size: 0.9rem; color: #94a3b8; margin-bottom: 1rem; }

    @media (max-width: 640px) {
        .action-btn { padding: .35rem .65rem; font-size: 0.78rem; }
        .av-wrap, .av-init { width: 34px; height: 34px; font-size: 0.8rem; }
        .users-table td { padding: 0.65rem 0.4rem !important; }
    }
</style>
@endpush

@section('content')
<div class="panel-card">
    <div class="panel-head">
        <h4><i class="fa fa-users" style="color:#1d4ed8;margin-right:6px;"></i> Manage Users</h4>
        <small>{{ $users->total() }} total users</small>
    </div>
    <div style="padding: 1rem 1.25rem;">

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('admin.users') }}" class="search-bar-row">
            <div class="search-input-wrap">
                <i class="fa fa-search"></i>
                <input class="search-input" type="text" name="search"
                       value="{{ $search ?? '' }}" placeholder="Search by name or email…">
            </div>
            <button type="submit" class="btn-search"><i class="fa fa-search"></i> Search</button>
            @if($search)
                <a href="{{ route('admin.users') }}" class="btn-clear"><i class="fa fa-times"></i> Clear</a>
            @endif
        </form>

        @if($search)
            <p class="results-info">Showing results for "<strong>{{ $search }}</strong>"</p>
        @endif

        {{-- Desktop Table View --}}
        <div class="desktop-only-table" style="overflow-x:auto;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Shares</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td style="color:#94a3b8;font-size:.72rem;">#{{ $user->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                @if($user->profile_picture || $user->avatar)
                                    <img class="av-img" style="width:34px;height:34px;border-radius:8px;object-fit:cover;"
                                         src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : $user->avatar }}"
                                         alt="{{ $user->name }}">
                                @else
                                    <div class="av-init">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                @endif
                                <span style="font-weight:600;color:#0f172a;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td style="text-align:center;font-weight:600;">{{ $user->shares_count ?? 0 }}</td>
                        <td>
                            @if($user->is_banned)
                                <span class="badge-banned">Banned</span>
                            @else
                                <span class="badge-active">Active</span>
                            @endif
                        </td>
                        <td style="color:#64748b;">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="action-btn {{ $user->is_banned ? 'btn-unban' : 'btn-ban' }}">
                                        <i class="fa fa-{{ $user->is_banned ? 'check' : 'ban' }}"></i>
                                        {{ $user->is_banned ? 'Unban' : 'Ban' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                                      onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');" style="display:inline;">
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
                        <td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">
                            @if($search) No users match "{{ $search }}" @else No users found. @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View --}}
        <div class="mobile-only-card-list">
            @forelse($users as $user)
            <div class="mob-card">
                <div class="mob-card-head">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
                        @if($user->profile_picture || $user->avatar)
                            <img style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0;"
                                 src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : $user->avatar }}"
                                 alt="{{ $user->name }}">
                        @else
                            <div class="av-init" style="width:40px;height:40px;border-radius:10px;font-size:0.95rem;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                        <div style="min-width:0;flex:1;">
                            <div class="mob-card-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->name }}</div>
                            <div class="mob-card-sub" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                        <span style="font-size:0.72rem;color:#94a3b8;font-weight:700;">#{{ $user->id }}</span>
                        @if($user->is_banned)
                            <span class="badge-banned">Banned</span>
                        @else
                            <span class="badge-active">Active</span>
                        @endif
                    </div>
                </div>
                <div class="mob-card-meta">
                    <span><i class="fa fa-share-alt" style="color:#1d4ed8;margin-right:4px;"></i> {{ $user->shares_count ?? 0 }} shares</span>
                    <span><i class="fa fa-calendar" style="color:#64748b;margin-right:4px;"></i> Joined {{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="mob-card-actions">
                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="action-btn {{ $user->is_banned ? 'btn-unban' : 'btn-ban' }}">
                            <i class="fa fa-{{ $user->is_banned ? 'check' : 'ban' }}"></i>
                            {{ $user->is_banned ? 'Unban' : 'Ban' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                          onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn btn-del">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:2rem;color:#94a3b8;background:#fff;border-radius:12px;border:1px solid #e2e8f0;">
                @if($search) No users match "{{ $search }}" @else No users found. @endif
            </div>
            @endforelse
        </div>

        <div style="margin-top:1rem;">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
