@extends('layouts.admin')

@section('page-body')

<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row size-column">
        <div class="col-xxl-12 box-col-12">
            <!-- Dashboard Content -->
            @php
            $user = auth()->user();
            @endphp

            @if ($user->hasRole('superadmin'))
            <!-- Super Admin Dashboard Content -->
            @elseif ($user->hasRole('admin'))
            <!-- Admin Dashboard Content -->
            @elseif ($user->hasRole('salesmanager'))
            <!-- Sales Manager Dashboard Content -->
            @elseif ($user->hasRole('distributor'))
            <!-- Distributor Dashboard Content -->
            @elseif ($user->hasRole('fieldstaff'))
            <!-- Field Staff Dashboard Content -->
            @elseif ($user->hasRole('retailer'))
            <!-- Retailer Dashboard Content -->
            @endif
        </div>
    </div>
</div>

@endsection