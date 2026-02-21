@extends('layouts.app')
@section('title', 'Create User')

@section('content')
    <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between py-3">
            <h5 class="card-title m-0 me-2 text-white">Add User</h5>
        </div>
        <form action="{{ route('user.store') }}" method="post" enctype="multipart/form-data" id="userCreate" data-parsley-validate>
            @csrf
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <input autocomplete="off" type="text" class="form-control" name="first_name"
                                value="{{ old('first_name') }}" id="first_name" placeholder="Enter First Name" required
                                data-parsley-required-message="First name is required." />
                            <label for="first_name">First Name</label>
                            @error('first_name')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <input autocomplete="off" type="text" class="form-control" name="last_name"
                                value="{{ old('last_name') }}" id="last_name" placeholder="Enter Last Name" required
                                data-parsley-required-message="Last name is required." />
                            <label for="last_name">Last Name</label>
                            @error('last_name')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <select class="form-select select2" id="role_id" name="role_id" required
                                data-parsley-required-message="Role is required."
                                data-parsley-errors-container="#role_id_err">
                                <option value="" selected>Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <label for="role_id">Role</label>
                            <small class="red-text ml-10" id="role_id_err" role="alert"></small>
                            @error('role_id')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <input autocomplete="off" type="email" class="form-control" name="email"
                                value="{{ old('email') }}" id="email" placeholder="Enter Email" required
                                data-parsley-required-message="Email is required."
                                data-parsley-type-message="Please enter a valid email address." />
                            <label for="email">Email</label>
                            @error('email')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <input autocomplete="off" type="text" class="form-control" name="phone_number"
                                value="{{ old('phone_number') }}" id="phone_number" placeholder="Enter Phone Number"
                                data-parsley-pattern="^[0-9+\-\s()]{7,20}$"
                                data-parsley-pattern-message="Phone number must be 7 to 20 valid characters." />
                            <label for="phone_number">Phone Number</label>
                            @error('phone_number')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <input autocomplete="off" type="text" class="form-control flatpickr-date" name="date_of_birth"
                                value="{{ old('date_of_birth') }}" id="date_of_birth" placeholder="YYYY-MM-DD"
                                data-parsley-pattern="^(|\\d{4}-\\d{2}-\\d{2})$"
                                data-parsley-pattern-message="Use date format YYYY-MM-DD." />
                            <label for="date_of_birth">Date of Birth</label>
                            @error('date_of_birth')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-floating form-floating-outline mb-4">
                            <select class="form-select select2" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" @selected(old('gender') === 'male')>Male</option>
                                <option value="female" @selected(old('gender') === 'female')>Female</option>
                                <option value="other" @selected(old('gender') === 'other')>Other</option>
                            </select>
                            <label for="gender">Gender</label>
                            @error('gender')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="mb-4">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                            @error('profile_photo')
                                <small class="red-text ml-10" role="alert">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end pt-0">
                <a href="{{ route('user.index') }}" class="btn btn-outline-secondary waves-effect waves-light me-1">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/parsley.min.js') }}"></script>
    <script>
        $(function() {
            var userCreateForm = $('#userCreate').parsley({
                excluded: 'input[type=button], input[type=submit], input[type=reset], [disabled]'
            });

            $('#role_id, #gender').on('change', function() {
                userCreateForm.validate();
            });

            $('.flatpickr-date').flatpickr({
                dateFormat: 'Y-m-d',
                maxDate: 'today',
                allowInput: true
            });
        });
    </script>
@endsection
