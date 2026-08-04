@extends('layouts.admin')

@section('title', 'Accuracy Result')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="x_panel" style="padding: 0; border: none; background: transparent; box-shadow: none;">
            <div class="x_content" style="padding: 0; margin-top: 0;">
                <iframe src="{{ route('admin.algo-test-suite.frame') }}" style="width: 100%; height: calc(100vh - 160px); min-height: 480px; border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);" id="algo-frame"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
