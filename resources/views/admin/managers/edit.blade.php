@extends('layouts.admin')
@section('page-body')
<div class="container p-4">
    <h2>Edit Manager</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('managers.update', $manager->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $manager->name) }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $manager->email) }}" required>
        </div>

        <div class="mb-3">
            <label>Password (leave blank to keep same)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <button class="btn btn-success">Update Manager</button>
    </form>
</div>
@endsection
