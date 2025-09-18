@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Roles</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>{{ $role->permissions->pluck('name')->join(', ') }}</td>
                <td><a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
