@extends('Admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Change Password</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    @include('Admin.message')
    <div class="container-fluid">
        <form action="#" method="post" id="changePasswordForm" name="changePasswordForm">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Old Password</label>
                                <input type="password" name="old_password" id="old_password" placeholder="Old Password"
                                    class="form-control">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">New Password</label>
                                <input type="password" name="new_password" id="new_password" placeholder="New Password"
                                    class="form-control">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password"
                                    placeholder="Old Password" class="form-control">
                                <p></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection

@section('customJs')
<script>
    $(document).ready(function() {
    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();
        $("button[type=submit]").prop('disabled', true);

        $.ajax({
            url: '{{ route('admin.changePassword') }}',
            type: 'post',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $("button[type=submit]").prop('disabled', false);

                if (response.status == true) {
                    window.location.href = '{{ route('admin.showChangePassword') }}';
                } else {
                    var errors = response.errors;

                    if (errors.old_password) {
                        $("#old_password").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.old_password[0]);
                    } else {
                        $("#old_password").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if (errors.new_password) {
                        $("#new_password").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.new_password[0]);
                    } else {
                        $("#new_password").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if (errors.confirm_password) {
                        $("#confirm_password").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.confirm_password[0]);
                    } else {
                        $("#confirm_password").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                }
            },
            error: function(jQXHR, exception) {
                console.log('Something went wrong');
                console.log(jQXHR.responseText);
                $("button[type=submit]").prop('disabled', false);
            }
        });
    });
});


</script>
@endsection