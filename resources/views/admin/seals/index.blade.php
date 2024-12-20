<!-- resources/views/admin/seals/index.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Seals</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.seals.create') }}" class="btn btn-primary mb-3">Create New Seal</a>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seals as $seal)
                <tr>
                    <td>{{ $seal->name }}</td>
                    <td>{{ $seal->slug }}</td>
                    <td>
                        <img src="{{ asset('storage/' . $seal->image) }}" alt="{{ $seal->name }}" width="50">
                    </td>
                    <td>
                        <a href="{{ route('admin.seals.show', $seal->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admin.seals.edit', $seal->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('admin.seals.destroy', $seal->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this seal?');" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

