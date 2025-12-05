@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Users <small>View, Ban, or Delete</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">ID </th>
                                <th class="column-title">Name </th>
                                <th class="column-title">Email </th>
                                <th class="column-title">Joined </th>
                                <th class="column-title no-link last"><span class="nobr">Action</span></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($users as $user)
                            <tr class="even pointer">
                                <td class=" ">{{ $user->id }}</td>
                                <td class=" ">{{ $user->name }}</td>
                                <td class=" ">{{ $user->email }}</td>
                                <td class=" ">{{ $user->created_at->format('Y-m-d') }}</td>
                                <td class=" last">
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                                    </form>
                                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-xs">
                                            <i class="fa fa-ban"></i> {{ $user->is_banned ? 'Unban' : 'Ban' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
