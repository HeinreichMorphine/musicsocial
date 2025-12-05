@extends('layouts.admin')

@section('title', 'Moderate Content')

@section('content')
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Shares <small>Recent Shares</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <ul class="list-unstyled msg_list">
                    @foreach($shares as $share)
                    <li>
                        <a>
                            <span class="image">
                                <!-- Placeholder for user image -->
                                <img src="{{ asset('assets/admin/production/images/img.jpg') }}" alt="img" />
                            </span>
                            <span>
                                <span>{{ $share->user->name ?? 'Unknown' }}</span>
                                <span class="time">{{ $share->created_at->diffForHumans() }}</span>
                            </span>
                            <span class="message">
                                <strong>{{ $share->song->track_name ?? 'Unknown Song' }}</strong><br>
                                {{ Str::limit($share->caption ?? 'No caption', 100) }}
                            </span>
                        </a>
                        <div class="text-right">
                            <form action="{{ route('admin.shares.delete', $share->id) }}" method="POST" onsubmit="return confirm('Delete this share?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                {{ $shares->links() }}
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Comments <small>Recent Comments</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <ul class="list-unstyled msg_list">
                    @foreach($comments as $comment)
                    <li>
                        <a>
                            <span class="image">
                                <img src="{{ asset('assets/admin/production/images/img.jpg') }}" alt="img" />
                            </span>
                            <span>
                                <span>{{ $comment->user->name ?? 'Unknown' }}</span>
                                <span class="time">{{ $comment->created_at->diffForHumans() }}</span>
                            </span>
                            <span class="message">
                                {{ Str::limit($comment->body ?? 'No comment text', 100) }}
                            </span>
                        </a>
                        <div class="text-right">
                            <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" onsubmit="return confirm('Delete this comment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                {{ $comments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
