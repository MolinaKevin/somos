{{-- resources/views/admin/cashouts/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Cashout #{{ $cashout->id }}</h1>

    <form action="{{ route('admin.cashouts.update', $cashout->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="commerce_id" class="form-label">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-select" required>
                @foreach ($commerces as $commerce)
                    <option value="{{ $commerce->id }}" {{ $cashout->commerce_id == $commerce->id ? 'selected' : '' }}>
                        {{ $commerce->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" name="amount" id="amount" class="form-control" value="{{ $cashout->amount }}" required min="0" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Cashout</button>
    </form>
</div>
@endsection

