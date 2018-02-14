@extends('spark::layouts.app')

@section('content')
<div class="container">
    <!-- Application Dashboard -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-default">
                <div class="card-header">{{__('Terms Of Service')}}</div>

                <div class="card-body terms-of-service">
                    {!! $terms !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
