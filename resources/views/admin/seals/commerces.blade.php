@extends('layouts.app')

@section('content')
    <h1>Commerces Associated with Seal: {{ $seal->name }}</h1>

    <a href="{{ route('admin.seals.show', $seal->id) }}" class="btn btn-secondary mb-3">Back to Seal Details</a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($commerces->isEmpty())
        <p>No commerces are currently associated with this seal.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commerces as $commerce)
                    <tr>
                        <td>{{ $commerce->name }}</td>
                        <td>
                            <!-- You can add actions specific to the commerce here -->
                            <a href="{{ route('admin.commerces.show', $commerce->id) }}" class="btn btn-info btn-sm">View Commerce</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 class="mt-4">Associate More Commerces</h2>
    <form action="{{ route('admin.seals.associateCommerces', $seal->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="commerce_ids">Select Commerces:</label>
            <select name="commerce_ids[]" id="commerce_ids" class="form-control" multiple>
                @foreach($availableCommerces as $commerce)
                    <option value="{{ $commerce->id }}">{{ $commerce->name }}</option>
                @endforeach
            </select>
            @error('commerce_ids')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Associate Commerces</button>
    </form>
@endsection

