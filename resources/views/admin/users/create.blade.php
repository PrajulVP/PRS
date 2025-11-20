@extends('layouts.admin')

@section('page-body')
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Add New User</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="name">Name</label>
                                <input class="form-control" id="name" type="text" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email address</label>
                                <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" id="password" type="password" name="password" required>
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password_confirmation">Confirm Password</label>
                                <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="role">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="profile_pic">Profile Picture</label>
                                <input class="form-control" id="profile_pic" type="file" name="profile_pic">
                                @error('profile_pic')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Conditional Distributor Dropdown --}}
                            <div class="mb-3" id="distributor-selection" style="display: none;">
                                <label class="form-label" for="distributor_id">Assign to Distributor</label>
                                <select class="form-select" id="distributor_id" name="distributor_id">
                                    <option value="">-- Select Distributor --</option>
                                    @foreach ($distributors as $distributor)
                                        <option value="{{ $distributor->id }}" {{ old('distributor_id') == $distributor->id ? 'selected' : '' }}>
                                            {{ $distributor->user->name ?? $distributor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('distributor_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-primary" type="submit">Create User</button>
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const distributorSelectionDiv = document.getElementById('distributor-selection');
        const distributorSelect = document.getElementById('distributor_id');

        function toggleDistributorSelection() {
            const selectedRole = roleSelect.value;
            if (['manager', 'fieldstaff', 'retailer'].includes(selectedRole)) {
                distributorSelectionDiv.style.display = 'block';
                // For manager, distributor_id is nullable. For fieldstaff/retailer, it's required.
                // We'll handle 'required' status via server-side validation.
                distributorSelect.setAttribute('required', 'required'); // Make it required in UI for these roles
            } else {
                distributorSelectionDiv.style.display = 'none';
                distributorSelect.removeAttribute('required');
                distributorSelect.value = ''; // Clear selection when hidden
            }
        }

        // Initial call to set visibility based on initial selected role
        toggleDistributorSelection();

        // Listen for changes on the role select dropdown
        roleSelect.addEventListener('change', toggleDistributorSelection);
    });
</script>
@endpush