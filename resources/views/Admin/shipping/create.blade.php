@extends('Admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Shipping Management</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="#" class="btn btn-primary">Back</a>
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
        <form action="#" method="post" id="shippingForm" name="shippingForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <select name="country" id="country" class="form-control">
                                    <option value="">Select a Country</option>
                                    @if ($countries)
                                    @foreach ($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                    <option value="rest_of_world">Rest of The World</option>
                                    @endif
                                </select>
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <input type="text" name="amount" id="amount" class="form-control" placeholder="Amount">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped">
                                <tr>
                                    <th>ID</th>
                                    <th>Country</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                                @if ($shippingCharges )
                                @foreach ($shippingCharges as $shipping)
                                <tr>
                                    <td>{{ $shipping->id }}</td>
                                    <td>{{ ($shipping->country_id == 'rest_of_world') ? 'Rest of The World' :
                                        $shipping->name }}</td>
                                    <td>${{ $shipping->amount }}</td>
                                    <td class="">
                                        <a href="{{ route('shipping.edit', $shipping->id) }} "
                                            class="btn btn-primary">Edit</a>
                                        <a href="javascript:void(0);" onclick="deleteRecord({{ $shipping->id }});"
                                            class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection

@section('customJs')
<script>
    $("#shippingForm").submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: '{{ route('shipping.store') }}',
                type: 'post',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);

                    if (response['status'] == true) {
                        window.location.href = "{{ route('shipping.create') }}";

                    } else {
                        var errors = response['errors'];
                        if (errors['country']) {
                            $("#country").addClass('is-invalid').siblings('p').addClass(
                                    'invalid-feedback')
                                .html(errors['country']);
                        } else {
                            $("#country").removeClass('is-invalid').siblings('p').removeClass(
                                'invalid-feedback').html("");
                        }

                        if (errors['amount']) {
                            $("#amount").addClass('is-invalid').siblings('p').addClass(
                                    'invalid-feedback')
                                .html(errors['amount']);
                        } else {
                            $("#amount").removeClass('is-invalid').siblings('p').removeClass(
                                'invalid-feedback').html("");
                        }
                    }
                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong");
                }
            });
        });

        function deleteRecord(id) {
        if (confirm('Are you sure you want to delete this record?')) {
            var url = '{{ route('shipping.delete', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    if (response['status']) {
                        window.location.href = "{{ route('shipping.create') }}";
                    }
                }
            });
        }
    }
</script>
@endsection