@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="">
    <div class="row top_tiles">
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="tile-stats">
          <div class="icon"><i class="fa fa-users"></i></div>
          <div class="count">{{ $userCount }}</div>
          <h3>Total Users</h3>
          <p>Registered users on the platform.</p>
        </div>
      </div>
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="tile-stats">
          <div class="icon"><i class="fa fa-share-alt"></i></div>
          <div class="count">{{ $shareCount }}</div>
          <h3>Total Shares</h3>
          <p>Music shares posted by users.</p>
        </div>
      </div>
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="tile-stats">
          <div class="icon"><i class="fa fa-comments"></i></div>
          <div class="count">{{ $commentCount }}</div>
          <h3>Total Comments</h3>
          <p>Comments made on shares.</p>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Analytics <small>System Overview</small></h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <p>Welcome to the MusicSocial Admin Dashboard. Use the sidebar to manage users and moderate content.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
