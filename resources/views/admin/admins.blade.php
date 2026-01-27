@extends('layouts.admin')

@section('title', 'Manage Admins')

@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Admins <small>Create and Remove Administrators</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                
                <!-- Create Admin Form -->
                <div class="row">
                    <div class="col-md-12">
                        <h4>Add New Admin</h4>
                        <form action="{{ route('admin.admins.store') }}" method="POST" class="form-horizontal form-label-left">
                            @csrf
                            <div class="form-group row">
                                <div class="col-md-3 col-sm-3">
                                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="col-md-3 col-sm-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm" required>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <button type="submit" class="btn btn-success btn-block">Add Admin</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ln_solid"></div>

                <!-- Admins List -->
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">ID </th>
                                <th class="column-title">Name </th>
                                <th class="column-title">Email </th>
                                <th class="column-title">Created At </th>
                                <th class="column-title no-link last"><span class="nobr">Action</span></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($admins as $admin)
                            <tr class="even pointer">
                                <td class=" ">{{ $admin->id }}</td>
                                <td class=" ">{{ $admin->name }} @if(Auth::guard('admin')->id() == $admin->id) (You) @endif</td>
                                <td class=" ">{{ $admin->email }}</td>
                                <td class=" ">{{ $admin->created_at->format('Y-m-d H:i') }}</td>
                                <td class=" last">
                                    @if(Auth::guard('admin')->id() != $admin->id)
                                    <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                                    </form>
                                    @else
                                    <span class="badge badge-info">Current Session</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
