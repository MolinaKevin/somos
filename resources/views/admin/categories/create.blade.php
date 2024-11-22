{{-- resources/views/admin/purchases/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Compra</h1>

    <form action="{{ route('admin.purchases.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="commerce_id" class="form-label">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-select" required>
                <option value="">Seleccione un comercio</option>
                @foreach ($commerces as $commerce)
                    <option value="{{ $commerce->id }}">{{ $commerce->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="user_id" class="form-label">Usuario</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">Seleccione un usuario</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" name="amount" id="amount" class="form-control" required min="0" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary">Crear Compra</button>
    </form>
</div>
@endsection

