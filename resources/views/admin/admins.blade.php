@extends('layouts.admin')

@section('title', 'Manage Admins')

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

    .add-form-card {
        background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px;
        padding: 1.5rem; margin: 1.5rem;
    }
    .add-form-title { font-size: 0.95rem; font-weight: 700; color: #374151; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: .5px; }
    .add-form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 0.8rem; align-items: end; }
    @media (max-width: 900px) { .add-form-grid { grid-template-columns: 1fr 1fr; } }

    .field-group label { display: block; font-size: 0.82rem; font-weight: 600; color: #64748b; margin-bottom: .45rem; text-transform: uppercase; letter-spacing: .4px; }
    .field-group input {
        width: 100%; padding: .7rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.95rem; color: #0f172a; outline: none; transition: border-color .2s;
        background: #fff;
    }
    .field-group input:focus { border-color: #1d4ed8; }
    .btn-add {
        padding: .75rem 1.5rem; background: #1d4ed8; color: #fff; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        white-space: nowrap; transition: background .15s;
    }
    .btn-add:hover { background: #1e40af; }

    .divider { height: 1px; background: #f1f5f9; margin: 0; }

    .admins-table { width: 100%; border-collapse: collapse; }
    .admins-table thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .admins-table th {
        padding: 0.9rem 1.25rem; text-align: left; font-size: 0.82rem;
        font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px;
    }
    .admins-table td {
        padding: 0.9rem 1.25rem; font-size: 0.95rem; color: #374151;
        border-bottom: 1px solid #f8fafc; vertical-align: middle;
    }
    .admins-table tbody tr:last-child td { border-bottom: none; }
    .admins-table tbody tr:hover { background: #fafbff; }

    .admin-av {
        width: 40px; height: 40px; border-radius: 8px;
        background: #1d4ed8; color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 800; margin-right: 0.8rem;
    }
    .badge-you { background: #eff6ff; color: #1d4ed8; font-size: 0.8rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px; }
    .btn-del-sm {
        padding: .45rem .85rem; background: #fef2f2; color: #dc2626;
        border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; transition: opacity .15s;
    }
    .btn-del-sm:hover { opacity: .8; }

    @media (max-width: 640px) {
        .add-form-card { margin: 0.85rem 0.5rem; padding: 0.85rem; }
        .add-form-grid { grid-template-columns: 1fr !important; }
        .btn-del-sm { padding: .35rem .65rem; font-size: 0.78rem; }
        .admin-av { width: 34px; height: 34px; font-size: 0.75rem; margin-right: 0.5rem; }
        .admins-table td { padding: 0.65rem 0.4rem !important; }
    }
</style>
@endpush

@section('content')
<div class="panel-card">
    <div class="panel-head">
        <h4><i class="fa fa-shield" style="color:#1d4ed8;margin-right:6px;"></i> Manage Admins</h4>
        <small>{{ $admins->count() }} administrator{{ $admins->count() !== 1 ? 's' : '' }}</small>
    </div>

    {{-- Add Admin Form --}}
    <div class="add-form-card">
        <div class="add-form-title"><i class="fa fa-plus" style="margin-right:5px;"></i> Add New Administrator</div>
        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf
            <div class="add-form-grid">
                <div class="field-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Jane Smith" required>
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="admin@reso.app" required>
                </div>
                <div class="field-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 8 chars" required>
                </div>
                <div class="field-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Repeat password" required>
                </div>
                <div>
                    <button type="submit" class="btn-add"><i class="fa fa-user-plus"></i> Add Admin</button>
                </div>
            </div>
        </form>
    </div>

    <div class="divider"></div>

    {{-- Desktop Admins Table --}}
    <div class="desktop-only-table" style="overflow-x:auto;">
        <table class="admins-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td style="color:#94a3b8;font-size:.72rem;">#{{ $admin->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;">
                            <div class="admin-av">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
                            <div>
                                <div style="font-weight:600;color:#0f172a;">{{ $admin->name }}</div>
                                @if(Auth::guard('admin')->id() == $admin->id)
                                    <span class="badge-you">Current Session</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td style="color:#64748b;">{{ $admin->created_at->format('d M Y') }}</td>
                    <td>
                        @if(Auth::guard('admin')->id() != $admin->id)
                            <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST"
                                  onsubmit="return confirm('Remove admin access for {{ addslashes($admin->name) }}?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-sm"><i class="fa fa-trash"></i> Remove</button>
                            </form>
                        @else
                            <span style="font-size:.75rem;color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile Admins Cards --}}
    <div class="mobile-only-card-list" style="padding: 12px;">
        @foreach($admins as $admin)
        <div class="mob-card">
            <div class="mob-card-head">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="admin-av" style="margin-right:0;">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
                    <div>
                        <div class="mob-card-title">{{ $admin->name }}</div>
                        <div class="mob-card-sub">{{ $admin->email }}</div>
                    </div>
                </div>
                <div>
                    @if(Auth::guard('admin')->id() == $admin->id)
                        <span class="badge-you">You</span>
                    @else
                        <span style="font-size:0.72rem;color:#94a3b8;font-weight:700;">#{{ $admin->id }}</span>
                    @endif
                </div>
            </div>
            <div class="mob-card-sub"><i class="fa fa-calendar"></i> Added {{ $admin->created_at->format('d M Y') }}</div>
            @if(Auth::guard('admin')->id() != $admin->id)
            <div class="mob-card-actions">
                <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST"
                      onsubmit="return confirm('Remove admin access for {{ addslashes($admin->name) }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del-sm"><i class="fa fa-trash"></i> Remove Admin</button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
