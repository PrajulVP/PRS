
@extends('layouts.admin')

@section('page-body')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Admin: {{ $admin->name }}</h1>
            <table class="table">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $admin->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $admin->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $admin->email }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
