@extends('Front.Layouts.app')
@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
                <li class="breadcrumb-item"><a class="white-text" href="#">My Account</a></li>
                <li class="breadcrumb-item">Settings</li>
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
                        <h2 class="h5 mb-0 pt-2 pb-2">Personal Information</h2>
                    </div>
                    <form action="" id="profileForm" name="profileForm">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input value="{{ $user->name }}" type="text" name="name" id="name"
                                        placeholder="Enter Your Name" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="email">Email</label>
                                    <input value="{{ $user->email }}" type="text" name="email" id="email"
                                        placeholder="Enter Your Email" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="phone">Phone</label>
                                    <input value="{{ $user->phone }}" type="text" name="phone" id="phone"
                                        placeholder="Enter Your Phone" class="form-control">
                                    <p></p>
                                </div>

                                <div class="d-flex">
                                    <button type="submit" class="btn btn-dark">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="h5 mb-0 pt-2 pb-2">Address</h2>
                    </div>
                    <form action="" id="addressForm" name="addressForm">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="first_name">First Name</label>
                                    <input value="{{ (!empty($address)) ? $address->first_name : '' }}" type="text"
                                        name="first_name" id="first_name" placeholder="Enter Your Fist Name"
                                        class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="last_name">Last Name</label>
                                    <input value="{{ (!empty($address)) ? $address->last_name : '' }}" type="text"
                                        name="last_name" id="last_name" placeholder="Enter Your Last Name"
                                        class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="email">Email</label>
                                    <input value="{{ (!empty($address)) ? $address->email : '' }}" type="text"
                                        name="email" id="email" placeholder="Enter Your Email" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="mobile">Mobile</label>
                                    <input value="{{ (!empty($address)) ? $address->mobile : '' }}" type="text"
                                        name="mobile" id="mobile" placeholder="Enter Your Mobile" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile">Country</label>
                                    <select name="country_id" id="country_id" class="form-control">
                                        <option>Select a Country</option>
                                        @if ($countries->isNotEmpty())
                                        @foreach ($countries as $country)
                                        <option {{ (!empty($address) && $address->country_id == $country->id) ?
                                            'selected' : '' }} value="{{ $country->id }}">{{ $country->name }}
                                        </option>
                                        @endforeach
                                        @else
                                        <option value="">No Country Found</option>
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                                <div class="mb-3">
                                    <label for="address">Address</label>
                                    <Textarea type="text" name="address" id="address" placeholder="Enter Your Address"
                                        class="form-control">{{ (!empty($address)) ? $address->address : '' }}</Textarea>
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="apartment">Apartment</label>
                                    <input value="{{ (!empty($address)) ? $address->apartment : '' }}" type="text"
                                        name="apartment" id="apartment" placeholder="Enter Your Apartment"
                                        class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="city">City</label>
                                    <input value="{{ (!empty($address)) ? $address->city : '' }}" type="text"
                                        name="city" id="city" placeholder="Enter Your City" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="state">State</label>
                                    <input value="{{ (!empty($address)) ? $address->state : '' }}" type="text"
                                        name="state" id="state" placeholder="Enter Your State" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="zip">Zip</label>
                                    <input value="{{ (!empty($address)) ? $address->zip : '' }}" type="text" name="zip"
                                        id="zip" placeholder="Enter Your Zip" class="form-control">
                                    <p></p>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-dark">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('customJs')
<script>
    $(document).ready(function() {
            $('#profileForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('account.updateProfile') }}",
                    type: "post",
                    data: $(this).serializeArray(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status == true) {
                            window.location.href = "{{ route('account.profile') }}";
                        } else {
                            var errors = response['errors'];

                            if (errors['name']) {
                                $("#name").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['name']);
                            } else {
                                $("#name").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }

                            if (errors['email']) {
                                $("#email").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['email']);
                            } else {
                                $("#email").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['phone']) {
                                $("#phone").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['phone']);
                            } else {
                                $("#phone").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }

                        }
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#addressForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('account.updateAddress') }}",
                    type: "post",
                    data: $(this).serializeArray(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status == true) {
                            window.location.href = "{{ route('account.profile') }}";
                        } else {
                            var errors = response['errors'];

                            if (errors['first_name']) {
                                $("#first_name").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['first_name']);
                            } else {
                                $("#first_name").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }

                            if (errors['last_name']) {
                                $("#last_name").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['last_name']);
                            } else {
                                $("#last_name").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['email']) {
                                $("#addressForm #email").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['email']);
                            } else {
                                $("#addressForm #email").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['mobile']) {
                                $("#mobile").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['mobile']);
                            } else {
                                $("#mobile").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['country_id']) {
                                $("#country_id").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['country_id']);
                            } else {
                                $("#country_id").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['address']) {
                                $("#address").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['address']);
                            } else {
                                $("#address").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['apartment']) {
                                $("#apartment").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['apartment']);
                            } else {
                                $("#apartment").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['city']) {
                                $("#city").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['city']);
                            } else {
                                $("#city").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['state']) {
                                $("#state").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['state']);
                            } else {
                                $("#state").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                            if (errors['zip']) {
                                $("#zip").addClass('is-invalid').siblings('p').addClass(
                                        'invalid-feedback')
                                    .html(errors['zip']);
                            } else {
                                $("#zip").removeClass('is-invalid').siblings('p').removeClass(
                                    'invalid-feedback').html("");
                            }
                        }
                    }
                });
            });
        });
</script>
@endsection