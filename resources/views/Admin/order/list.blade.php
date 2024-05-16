@extends('Admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Categories</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('categories.create') }}" class="btn btn-primary">New Category</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        @include('Admin.message')
        <div class="card">
            <form action="" method="get">
                <div class="card-header">
                    <div class="card-title">
                        <button type="button" onclick="window.location.href='{{ route('orders.list') }}'"
                            class="btn btn-default btn-sm">
                            Reset
                        </button>
                    </div>
                    <div class="card-tools">
                        <div class="input-group input-group" style="width: 250px;">
                            <input type="text" value="{{ Request::get('keyword') }}" name="keyword"
                                class="form-control float-right" placeholder="Search">

                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date Purchased</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($orders->isNotEmpty())
                        @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('orders.detail', $order->id) }}">{{ $order->id }}</a></td>
                            <td>{{ $order->name }}</td>
                            <td>{{ $order->email }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>
                                @if ($order->status == 'pending')
                                <span class="badge bg-danger text-white">{{ $order->status }}</span>
                                @elseif ($order->status == 'shipped')
                                <span class="badge bg-primary">{{ $order->status }}</span>
                                @elseif ($order->status == 'delivered')
                                <span class="badge bg-success">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>${{ number_format($order->grand_total,2) }}</td>
                            <td>{{ $order->created_at->format('d M, Y') }}</td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="5">Records Not Found!!!</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">


                <ul class="pagination pagination m-0 float-right">
                    {{ $orders->links() }}
                </ul>
            </div>
        </div>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection

@section('customJs')
@endsection