{{-- resources/views/admin/cashouts/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Cashout</h1>

    <form action="{{ route('admin.cashouts.store') }}" method="POST">
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
            <label for="nro_id" class="form-label">NRO</label>
            <select name="nro_id" id="nro_id" class="form-select" required>
                <option value="">Seleccione un NRO</option>
                @foreach ($nros as $nro)
                    <option value="{{ $nro->id }}">{{ $nro->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" name="amount" id="amount" class="form-control" required min="0" step="0.01">
        </div>
        <button type="submit" class="btn btn-primary">Crear Cashout</button>
    </form>
</div>
@endsection

