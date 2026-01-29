@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="">
    <div class="row top_tiles">
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <a href="{{ route('admin.users') }}" style="text-decoration: none; color: inherit;">
            <div class="tile-stats">
            <div class="icon"><i class="fa fa-users"></i></div>
            <div class="count">{{ $userCount }}</div>
            <h3>Total Users</h3>
            <p>Registered users on the platform.</p>
            </div>
        </a>
      </div>
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <a href="{{ route('admin.moderation') }}" style="text-decoration: none; color: inherit;">
            <div class="tile-stats">
            <div class="icon"><i class="fa fa-share-alt"></i></div>
            <div class="count">{{ $shareCount }}</div>
            <h3>Total Shares</h3>
            <p>Music shares posted by users.</p>
            </div>
        </a>
      </div>
      <div class="animated flipInY col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <a href="{{ route('admin.moderation') }}" style="text-decoration: none; color: inherit;">
            <div class="tile-stats">
            <div class="icon"><i class="fa fa-comments"></i></div>
            <div class="count">{{ $commentCount }}</div>
            <h3>Total Comments</h3>
            <p>Comments made on shares.</p>
            </div>
        </a>
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
                    <div class="row">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>New Users <small>Recent Registrations</small></h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <ul class="list-unstyled msg_list">
                                        @foreach($latestUsers as $user)
                                        <li>
                                            <a>
                                                <span class="image">
                                                    <img src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : ($user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF') }}" alt="img" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;" />
                                                </span>
                                                <span>
                                                    <span><b>{{ $user->name }}</b></span>
                                                    <span class="time">{{ $user->created_at->diffForHumans() }}</span>
                                                </span>
                                                <span class="message" style="display: block; margin-top: 5px;">
                                                    {{ $user->email }}
                                                </span>
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Recent Shares <small>Latest Music</small></h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <ul class="list-unstyled msg_list">
                                        @foreach($latestShares as $share)
                                        <li>
                                            <a>
                                                <span>
                                                    <span><b>{{ $share->user->name }}</b> shared</span>
                                                    <span class="time">{{ $share->created_at->diffForHumans() }}</span>
                                                </span>
                                                <span class="message" style="display: block; margin-top: 5px;">
                                                    @if($share->song)
                                                        <i class="fa fa-music"></i> {{ $share->song->track_name }} - {{ $share->song->artist_name }}
                                                    @else
                                                        {{ Str::limit($share->caption, 50) }}
                                                    @endif
                                                </span>
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
