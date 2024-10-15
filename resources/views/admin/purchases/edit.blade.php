{{-- resources/views/admin/purchases/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Compra #{{ $purchase->id }}</h1>

    <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="commerce_id" class="form-label">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-select" required>
                @foreach ($commerces as $commerce)
                    <option value="{{ $commerce->id }}" {{ $purchase->commerce_id == $commerce->id ? 'selected' : '' }}>
                        {{ $commerce->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="user_id" class="form-label">Usuario</label>
            <select name="user_id" id="user_id" class="form-select" required>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $purchase->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" name="amount" id="amount" class="form-control" value="{{ $purchase->amount / 100 }}" required min="0" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Compra</button>
    </form>
</div>
@endsection

