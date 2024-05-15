@extends('Front.Layouts.app')
@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
                <li class="breadcrumb-item"><a class="white-text" href="{{ route('account.profile') }}">My Account</a>
                </li>
                <li class="breadcrumb-item">My Orders</li>
            </ol>
        </div>
    </div>
</section>

<section class=" section-11 ">
    <div class="container  mt-5">
        @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
        </div>
        @endif
        <div class="row">
            <div class="col-md-3">
                @include('Front.account.common.sidebar')
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0 pt-2 pb-2">My Orders</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Orders #</th>
                                        <th>Date Purchased</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($orders->isNotEmpty())
                                    @foreach ($orders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('account.orderDetail', $order->id) }}">{{
                                                $order->id }}</a>
                                        </td>
                                        <td>{{ $order->created_at->format('d M, Y') }}</td>
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
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="4" class="text-center">No Orders Found</td>
                                    </tr>
                                    @endif
                                    {{-- <tr>
                                        <td>
                                            <a href="order-detail.php">OR756374</a>
                                        </td>
                                        <td>11 Nav, 2022</td>
                                        <td>
                                            <span class="badge bg-success">Delivered</span>

                                        </td>
                                        <td>$400</td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection