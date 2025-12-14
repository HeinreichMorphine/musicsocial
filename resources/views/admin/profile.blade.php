@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<div class="">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Admin Profile <small>Edit Details</small></h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br />
                    <form action="{{ route('admin.profile.update') }}" method="POST" data-parsley-validate class="form-horizontal form-label-left">
                        @csrf

                        <div class="field item form-group">
                            <label class="col-form-label col-md-3 col-sm-3  label-align">Name<span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6">
                                <input class="form-control" data-validate-length-range="6" data-validate-words="2" name="name" placeholder="Full Name" required="required" value="{{ old('name', $admin->name) }}" />
                            </div>
                        </div>
                        
                        <div class="field item form-group">
                            <label class="col-form-label col-md-3 col-sm-3  label-align">Email<span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6">
                                <input class="form-control" name="email" class='email' required="required" type="email" value="{{ old('email', $admin->email) }}" />
                            </div>
                        </div>
                        
                        <div class="field item form-group">
                            <label class="col-form-label col-md-3 col-sm-3  label-align">New Password</label>
                            <div class="col-md-6 col-sm-6">
                                <input class="form-control" type="password" name="password" placeholder="Leave blank to keep current password" />
                            </div>
                        </div>
                        
                        <div class="field item form-group">
                            <label class="col-form-label col-md-3 col-sm-3  label-align">Confirm Password</label>
                            <div class="col-md-6 col-sm-6">
                                <input class="form-control" type="password" name="password_confirmation" />
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 offset-md-3">
                                <button type='submit' class="btn btn-primary">Update Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
