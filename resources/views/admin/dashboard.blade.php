@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    .kpi-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        display: flex; align-items: center; gap: 1.25rem;
        box-shadow: 0 1px 4px rgba(15,23,42,.04);
        transition: box-shadow .2s;
    }
    .kpi-card:hover { box-shadow: 0 4px 16px rgba(15,23,42,.08); }
    .kpi-icon {
        width: 56px; height: 56px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }
    .kpi-icon.blue   { background: #eff6ff; color: #1d4ed8; }
    .kpi-icon.green  { background: #f0fdf4; color: #16a34a; }
    .kpi-icon.purple { background: #faf5ff; color: #7c3aed; }
    .kpi-icon.orange { background: #fff7ed; color: #ea580c; }
    .kpi-icon.pink   { background: #fdf2f8; color: #db2777; }
    .kpi-val { font-size: 2.2rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .kpi-label { font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }

    .panels-row { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04);
    }
    .panel-head {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
    .panel-head small { font-size: 0.85rem; color: #94a3b8; }
    .panel-body { padding: 1.5rem; }

    /* Activity chart */
    .chart-wrap { position: relative; height: 280px; }

    /* Top genres list */
    .genre-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 0; border-bottom: 1px solid #f8fafc; font-size: 0.95rem;
    }
    .genre-row:last-child { border-bottom: none; }
    .genre-name { color: #374151; font-weight: 500; }
    .genre-count { background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 0.85rem; padding: .3rem .75rem; border-radius: 20px; }

    /* User list */
    .user-row {
        display: flex; align-items: center; gap: 1rem;
        padding: .85rem 0; border-bottom: 1px solid #f8fafc;
    }
    .user-row:last-child { border-bottom: none; }
    .user-av {
        width: 46px; height: 46px; border-radius: 8px; object-fit: cover; flex-shrink: 0;
    }
    .user-av-init {
        width: 46px; height: 46px; border-radius: 8px; background: #eff6ff;
        color: #1d4ed8; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.95rem; flex-shrink: 0;
    }
    .user-info-name { font-size: 1rem; font-weight: 600; color: #0f172a; }
    .user-info-email { font-size: 0.85rem; color: #94a3b8; }
    .user-time { font-size: 0.8rem; color: #cbd5e1; margin-left: auto; white-space: nowrap; }

    /* Share list */
    .share-row {
        display: flex; align-items: flex-start; gap: 1rem;
        padding: .85rem 0; border-bottom: 1px solid #f8fafc;
    }
    .share-row:last-child { border-bottom: none; }
    .share-icon {
        width: 46px; height: 46px; border-radius: 8px;
        background: #f0fdf4; color: #16a34a;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 1.1rem;
    }
    .share-song { font-size: 1rem; font-weight: 600; color: #0f172a; }
    .share-meta { font-size: 0.85rem; color: #94a3b8; }
    .share-time { font-size: 0.8rem; color: #cbd5e1; margin-left: auto; white-space: nowrap; }

    @media (max-width: 1200px) { .kpi-row { grid-template-columns: repeat(3,1fr); } }
    @media (max-width: 900px)  { .panels-row { grid-template-columns: 1fr; } .kpi-row { grid-template-columns: repeat(2,1fr); } }
</style>
@endpush

@section('content')

{{-- ── 5 KPI Tiles ─────────────────────────────────────────────────────── --}}
<div class="kpi-row">
    <a href="{{ route('admin.users') }}" style="text-decoration:none;">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fa fa-users"></i></div>
            <div>
                <div class="kpi-val">{{ number_format($userCount) }}</div>
                <div class="kpi-label">Total Users</div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.moderation') }}" style="text-decoration:none;">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fa fa-share-alt"></i></div>
            <div>
                <div class="kpi-val">{{ number_format($shareCount) }}</div>
                <div class="kpi-label">Total Shares</div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.moderation') }}" style="text-decoration:none;">
        <div class="kpi-card">
            <div class="kpi-icon purple"><i class="fa fa-comments"></i></div>
            <div>
                <div class="kpi-val">{{ number_format($commentCount) }}</div>
                <div class="kpi-label">Total Comments</div>
            </div>
        </div>
    </a>
    <div class="kpi-card">
        <div class="kpi-icon orange"><i class="fa fa-music"></i></div>
        <div>
            <div class="kpi-val">{{ number_format($songCount) }}</div>
            <div class="kpi-label">Songs in Catalog</div>
        </div>
    </div>
    <a href="{{ route('admin.retrain.page') }}" style="text-decoration:none;">
        <div class="kpi-card">
            <div class="kpi-icon pink"><i class="fa fa-list-ul"></i></div>
            <div>
                <div class="kpi-val">{{ number_format($playlistCount) }}</div>
                <div class="kpi-label">Total Playlists</div>
            </div>
        </div>
    </a>
</div>

{{-- ── Activity Chart + Top Genres ────────────────────────────────────── --}}
<div class="panels-row">

    {{-- 7-day Activity Chart --}}
    <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-line-chart" style="color:#1d4ed8;margin-right:6px;"></i> Shares Activity</h4>
            <small>Last 7 days</small>
        </div>
        <div class="panel-body">
            <div class="chart-wrap">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Genres --}}
    <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-tags" style="color:#7c3aed;margin-right:6px;"></i> Top Genres</h4>
            <small>By song count</small>
        </div>
        <div class="panel-body">
            @forelse($topGenres as $g)
                @php
                    try {
                        $names = json_decode($g->genres, true);
                        $label = is_array($names) ? implode(', ', array_slice($names, 0, 2)) : $g->genres;
                    } catch(\Exception $e) {
                        $label = $g->genres;
                    }
                @endphp
                <div class="genre-row">
                    <span class="genre-name">{{ Str::limit($label, 28) }}</span>
                    <span class="genre-count">{{ $g->count }}</span>
                </div>
            @empty
                <p style="color:#94a3b8;font-size:.82rem;">No genre data available yet.</p>
            @endforelse
        </div>
    </div>

</div>

{{-- ── New Users + Recent Shares ───────────────────────────────────────── --}}
<div class="panels-row">

    {{-- New Users --}}
    <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-user-plus" style="color:#16a34a;margin-right:6px;"></i> New Users</h4>
            <small>Most recent registrations</small>
        </div>
        <div class="panel-body">
            @foreach($latestUsers as $user)
            <div class="user-row">
                @if($user->profile_picture || $user->avatar)
                    <img class="user-av"
                         src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : $user->avatar }}"
                         alt="{{ $user->name }}">
                @else
                    <div class="user-av-init">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                @endif
                <div>
                    <div class="user-info-name">{{ $user->name }}
                        @if($user->is_banned)
                            <span style="background:#fef2f2;color:#dc2626;font-size:.65rem;padding:.15rem .4rem;border-radius:4px;font-weight:700;margin-left:4px;">BANNED</span>
                        @endif
                    </div>
                    <div class="user-info-email">{{ $user->email }}</div>
                </div>
                <span class="user-time">{{ $user->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Shares --}}
    <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-music" style="color:#ea580c;margin-right:6px;"></i> Recent Shares</h4>
            <small>Latest music posts</small>
        </div>
        <div class="panel-body">
            @foreach($latestShares as $share)
            <div class="share-row">
                <div class="share-icon"><i class="fa fa-music"></i></div>
                <div>
                    <div class="share-song">
                        {{ $share->song ? $share->song->track_name : Str::limit($share->caption ?? '—', 30) }}
                    </div>
                    <div class="share-meta">
                        {{ $share->song ? $share->song->artist_name : '' }}
                        · by <strong>{{ $share->user->name ?? 'Unknown' }}</strong>
                    </div>
                </div>
                <span class="share-time">{{ $share->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('activityChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($activityLabels) !!},
        datasets: [{
            label: 'Shares',
            data: {!! json_encode($activityData) !!},
            backgroundColor: 'rgba(29,78,216,0.12)',
            borderColor: '#1d4ed8',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} shares` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 }, color: '#94a3b8' }, grid: { color: '#f1f5f9' } }
        }
    }
});
</script>
@endpush
