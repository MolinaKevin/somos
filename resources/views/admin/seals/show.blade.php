@extends('layouts.app')

@section('content')
    <h1>Seal Details</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.seals.index') }}" class="btn btn-secondary">Back to Seals</a>
        <a href="{{ route('admin.seals.edit', $seal->id) }}" class="btn btn-warning">Edit Seal</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>{{ $seal->name }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Slug:</strong> {{ $seal->slug }}</p>
            <p><strong>Image:</strong></p>
            <img src="{{ asset('storage/' . $seal->image) }}" alt="{{ $seal->name }}" width="100">
        </div>
    </div>

    <h2 class="mt-4">Associated Commerces</h2>
    <a href="{{ route('admin.seals.commerces', $seal->id) }}" class="btn btn-info">View Associated Commerces</a>
@endsection

