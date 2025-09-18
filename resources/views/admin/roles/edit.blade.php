@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Role: {{ $role->name }}</h1>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div>
                @foreach($permissions as $permission)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
