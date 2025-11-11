@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Distributor to Order #{{ $retailerOrder->id }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.assign_distributor', $retailerOrder) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="distributor_id">Distributor</label>
                            <select name="distributor_id" id="distributor_id" class="form-control">
                                <option value="">Select Distributor</option>
                                @foreach ($distributors as $distributor)
                                    <option value="{{ $distributor->id }}">{{ $distributor->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Assign Distributor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
