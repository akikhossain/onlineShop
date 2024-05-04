@extends('Front.layouts.app')
@section('content')
<div class="container mt-5">
    @if (Session::has('success'))
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {!! Session::get('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    <div class="jumbotron">
        <h1 class="display-4">Thank You!</h1>
        <p class="lead">Your order ID: {{ $id}} has been successfully placed.</p>
        <hr class="my-4">
        <p>We appreciate your business.</p>
        <a class="btn btn-primary btn-lg" href="{{ route('front.home') }}" role="button">Back to Home</a>
    </div>
</div>
@endsection