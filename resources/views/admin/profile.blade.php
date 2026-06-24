@extends('layouts.admin')

@section('title', 'Admin Profile')

@push('styles')
<style>


    .panel-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15,23,42,.04);
    }
    .panel-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
    }
    .panel-head h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
    .panel-body { padding: 1.5rem; }

    /* Profile identity card */
    .profile-id-card {
        display: flex; flex-direction: column; align-items: center;
        padding: 2.5rem 1.75rem; text-align: center;
    }
    .profile-avatar {
        width: 100px; height: 100px; border-radius: 20px;
        background: linear-gradient(135deg, #1d4ed8 0%, #06b6d4 100%);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 800; margin-bottom: 1.25rem;
        box-shadow: 0 4px 20px rgba(29,78,216,.25);
    }
    .profile-name { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
    .profile-email { font-size: 0.9rem; color: #94a3b8; margin-top: .3rem; }
    .profile-divider { width: 100%; height: 1px; background: #f1f5f9; margin: 1.5rem 0; }
    .profile-meta-row {
        display: flex; justify-content: space-between; align-items: center;
        width: 100%; font-size: 0.9rem; padding: .5rem 0;
    }
    .profile-meta-label { color: #94a3b8; font-weight: 500; }
    .profile-meta-val { color: #374151; font-weight: 600; }
    .profile-role-badge {
        background: #eff6ff; color: #1d4ed8; padding: .3rem .75rem;
        border-radius: 20px; font-size: 0.85rem; font-weight: 700;
    }

    /* Form styles */
    .form-section-title {
        font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase;
        letter-spacing: .5px; margin-bottom: 1rem;
    }
    .field-group { margin-bottom: 1.25rem; }
    .field-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: .5rem; }
    .field-group input {
        width: 100%; padding: .75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.98rem; color: #0f172a; background: #f8fafc; outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .field-group input:focus { border-color: #1d4ed8; background: #fff; box-shadow: 0 0 0 3px rgba(29,78,216,.07); }
    .field-group input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }

    .form-divider { height: 1px; background: #f1f5f9; margin: 2rem 0; }

    .btn-save {
        padding: .75rem 1.75rem; background: #1d4ed8; color: #fff; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 700; cursor: pointer;
        transition: background .15s, box-shadow .15s;
    }
    .btn-save:hover { background: #1e40af; box-shadow: 0 4px 16px rgba(29,78,216,.2); }

    .btn-save-outline {
        padding: .75rem 1.75rem; background: #fff; color: #1d4ed8;
        border: 1.5px solid #1d4ed8; border-radius: 8px; font-size: 0.95rem;
        font-weight: 700; cursor: pointer; transition: background .15s;
    }
    .btn-save-outline:hover { background: #eff6ff; }
</style>
@endpush

@section('content')
<div class="row">

    {{-- Identity Card --}}
    <div class="col-md-4 col-sm-12" style="margin-bottom: 1.5rem;">
        <div class="panel-card">
        <div class="profile-id-card">
            <div class="profile-avatar">
                {{ strtoupper(substr($admin->name, 0, 2)) }}
            </div>
            <div class="profile-name">{{ $admin->name }}</div>
            <div class="profile-email">{{ $admin->email }}</div>

            <div class="profile-divider"></div>

            <div class="profile-meta-row">
                <span class="profile-meta-label">Role</span>
                <span class="profile-role-badge">Administrator</span>
            </div>
            <div class="profile-meta-row">
                <span class="profile-meta-label">Member since</span>
                <span class="profile-meta-val">{{ $admin->created_at->format('M Y') }}</span>
            </div>
            <div class="profile-meta-row">
                <span class="profile-meta-label">Admin ID</span>
                <span class="profile-meta-val">#{{ $admin->id }}</span>
            </div>
        </div>
    </div>
    </div>

    {{-- Edit Forms --}}
    <div class="col-md-8 col-sm-12" style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Update Profile Details --}}
        <div class="panel-card">
            <div class="panel-head">
                <h4><i class="fa fa-pencil" style="color:#1d4ed8;margin-right:6px;"></i> Profile Details</h4>
            </div>
            <div class="panel-body">
                <div class="form-section-title">Account Information</div>
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="field-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $admin->name) }}" required placeholder="Your full name">
                        </div>
                        <div class="field-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $admin->email) }}" required placeholder="admin@reso.app">
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fa fa-check" style="margin-right:5px;"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="panel-card">
            <div class="panel-head">
                <h4><i class="fa fa-lock" style="color:#7c3aed;margin-right:6px;"></i> Change Password</h4>
            </div>
            <div class="panel-body">
                <div class="form-section-title">Security</div>
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="field-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password"
                                   required placeholder="Min. 8 characters">
                        </div>
                        <div class="field-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   required placeholder="Repeat new password">
                        </div>
                    </div>
                    <button type="submit" class="btn-save-outline">
                        <i class="fa fa-lock" style="margin-right:5px;"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
