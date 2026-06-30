@extends('layouts.admin')

@section('title', 'AI Recommendations Preview')

@push('styles')
<style>


    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04);
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
    .panel-body { padding: 1.5rem; }

    /* Status badge */
    .api-status {
        display: inline-flex; align-items: center; gap: .6rem;
        padding: .45rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 700;
    }
    .api-status .dot {
        width: 10px; height: 10px; border-radius: 50%;
    }
    .api-status.online  { background: #f0fdf4; color: #16a34a; }
    .api-status.online .dot  { background: #16a34a; animation: pulse 1.5s infinite; }
    .api-status.offline { background: #fef2f2; color: #dc2626; }
    .api-status.offline .dot { background: #dc2626; }
    .api-status.checking { background: #fffbeb; color: #d97706; }
    .api-status.checking .dot { background: #d97706; }
    @keyframes pulse {
        0%,100% { opacity: 1; } 50% { opacity: .4; }
    }

    /* Audit shortcut cards */
    .audit-cards { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    .audit-card {
        padding: 1.25rem 1.5rem; border-radius: 12px; border: 1.5px solid #e2e8f0;
        text-decoration: none; display: flex; flex-direction: column; gap: .4rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .audit-card:hover { border-color: #1d4ed8; box-shadow: 0 4px 16px rgba(29,78,216,.08); text-decoration: none; }
    .audit-card-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
    .audit-card-desc { font-size: 0.82rem; color: #64748b; }
    .audit-card-icon { font-size: 1.5rem; margin-bottom: .4rem; }

    /* User selector form */
    .selector-row { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .user-select {
        flex: 1; min-width: 200px; padding: .75rem 1.25rem;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.95rem; color: #374151; outline: none;
        transition: border-color .2s;
    }
    .user-select:focus { border-color: #1d4ed8; }
    .btn-primary-sm {
        padding: .75rem 1.5rem; background: #1d4ed8; color: #fff; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .btn-primary-sm:hover { background: #1e40af; }
    .btn-warning-sm {
        padding: .75rem 1.5rem; background: #fff7ed; color: #ea580c;
        border: 1.5px solid #fed7aa; border-radius: 8px; font-size: 0.95rem;
        font-weight: 600; cursor: pointer; transition: background .15s;
    }
    .btn-warning-sm:hover { background: #ffedd5; }

    /* Recs table */
    .recs-table { width: 100%; border-collapse: collapse; }
    .recs-table thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .recs-table th {
        padding: 0.9rem 1.1rem; text-align: left;
        font-size: 0.82rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .recs-table td {
        padding: 0.9rem 1.1rem; font-size: 0.95rem; color: #374151;
        border-bottom: 1px solid #f8fafc; vertical-align: middle;
    }
    .recs-table tbody tr:last-child td { border-bottom: none; }
    .recs-table tbody tr:hover { background: #fafbff; }

    /* Score bar */
    .score-bar-wrap { display: flex; align-items: center; gap: .75rem; min-width: 140px; }
    .score-bar-track {
        flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;
    }
    .score-bar-fill {
        height: 100%; background: linear-gradient(90deg, #1d4ed8, #06b6d4);
        border-radius: 4px; transition: width .4s;
    }
    .score-val { font-size: 0.85rem; font-weight: 700; color: #1d4ed8; white-space: nowrap; }

    .debug-breakdown {
        font-size: 0.82rem; color: #94a3b8; line-height: 1.6;
    }
    .debug-breakdown strong { color: #374151; }
    .rank-badge {
        display: inline-flex; width: 30px; height: 30px; border-radius: 6px;
        align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 800;
        background: #eff6ff; color: #1d4ed8;
    }
    .rank-badge.gold   { background: #fefce8; color: #ca8a04; }
    .rank-badge.silver { background: #f8fafc; color: #64748b; }
    .rank-badge.bronze { background: #fff7ed; color: #ea580c; }
</style>
@endpush

@section('content')

<div class="row" style="margin-bottom: 1.5rem;">

    {{-- Flask API Status Card --}}
    <div class="col-md-6 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-server" style="color:#1d4ed8;margin-right:6px;"></i> Recommender Service Status</h4>
        </div>
        <div class="panel-body">
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                <span id="api-status-badge" class="api-status checking">
                    <span class="dot"></span> Checking…
                </span>
                <span id="api-version" style="font-size:.75rem;color:#94a3b8;"></span>
            </div>
            <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">
                Flask service at <code style="background:#f1f5f9;padding:.1rem .35rem;border-radius:4px;font-size:.75rem;">{{ env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000') }}</code>
                handles SVD collaborative filtering, TF-IDF content similarity, and social trust scoring.
            </p>
            <form action="{{ route('admin.retrain.process') }}" method="POST" style="display:inline-block;">
                @csrf
                <button type="submit" class="btn-warning-sm" onclick="return confirm('Force-retrain the SVD model now? This may take a few moments.');">
                    <i class="fa fa-refresh"></i> Force Retrain Model
                </button>
            </form>
        </div>
    </div>
    </div>

    {{-- Audit Dashboard Shortcuts --}}
    <div class="col-md-6 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="panel-head">
            <h4><i class="fa fa-bar-chart" style="color:#7c3aed;margin-right:6px;"></i> Audit Dashboard</h4>
        </div>
        <div class="panel-body">
            <p style="font-size:.8rem;color:#64748b;margin-bottom:.875rem;">
                Open the algorithm validation and benchmarking dashboard.
            </p>
            <div class="audit-cards">
                <a href="{{ route('admin.algo-test-suite') }}" class="audit-card">
                    <div class="audit-card-icon">🧪</div>
                    <div class="audit-card-title">Accuracy Result</div>
                    <div class="audit-card-desc">Global RMSE, NDCG@12, Precision@12 benchmarks</div>
                </a>
            </div>
        </div>
    </div>
    </div>

</div>

{{-- User Recs Preview --}}
<div class="panel-card">
    <div class="panel-head">
        <h4><i class="fa fa-eye" style="color:#16a34a;margin-right:6px;"></i> Preview Recommendation Feed</h4>
        <small>Select a user to see their personalised AI feed</small>
    </div>
    <div class="panel-body">
        <form action="{{ route('admin.retrain.page') }}" method="GET">
            <div class="selector-row">
                <select name="user_id" class="user-select">
                    <option value="">— Select a User —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} (ID: {{ $user->id }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary-sm"><i class="fa fa-play"></i> Get Recommendations</button>
            </div>
        </form>

        @if(isset($recommendations))
            <h5 style="font-size:.83rem;font-weight:700;color:#0f172a;margin:.5rem 0 .875rem;">
                Showing {{ count($recommendations) }} recommendations for
                <span style="color:#1d4ed8;">User #{{ request('user_id') }}</span>
            </h5>

            @if(count($recommendations) > 0)
            <div style="overflow-x:auto;">
                <table class="recs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Song</th>
                            <th>Reasoning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recommendations as $i => $rec)
                        <tr>
                            <td>
                                <span class="rank-badge {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td style="font-weight:600;color:#0f172a;">{{ $rec['song_name'] }}</td>
                            <td style="max-width:200px;color:#64748b;font-size:.77rem;">
                                {{ $rec['reason'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div style="padding:1.5rem;text-align:center;background:#f8fafc;border-radius:8px;color:#94a3b8;font-size:.83rem;">
                    <i class="fa fa-info-circle" style="margin-right:6px;"></i>
                    No recommendations found for this user. Ensure the recommender service is running and the model is trained.
                </div>
            @endif
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
// Live Flask API status check
(async () => {
    const badge = document.getElementById('api-status-badge');
    const verEl = document.getElementById('api-version');
    try {
        const res = await fetch('{{ route("admin.algo-test-suite.api", ["endpoint" => "stats"]) }}', { signal: AbortSignal.timeout(4000) });
        if (res.ok) {
            const data = await res.json();
            badge.className = 'api-status online';
            badge.innerHTML = '<span class="dot"></span> Online';
            if (data.algo_version) {
                verEl.textContent = `v${data.algo_version} · Last trained: ${data.last_train_time ?? 'N/A'}`;
            }
        } else {
            throw new Error('non-200');
        }
    } catch(e) {
        badge.className = 'api-status offline';
        badge.innerHTML = '<span class="dot"></span> Offline';
        verEl.textContent = 'Could not connect to Flask service on port 5000.';
    }
})();
</script>
@endpush
