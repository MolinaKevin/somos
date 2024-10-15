@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Compra de Puntos</h1>

    <form action="{{ route('admin.pointsPurchases.update', $pointsPurchase->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="user_id" class="form-label">Usuario</label>
            <select name="user_id" id="user_id" class="form-select">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $user->id == $pointsPurchase->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="commerce_id" class="form-label">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-select">
                @foreach($commerces as $commerce)
                    <option value="{{ $commerce->id }}" {{ $commerce->id == $pointsPurchase->commerce_id ? 'selected' : '' }}>{{ $commerce->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="points" class="form-label">Puntos</label>
            <input type="number" name="points" id="points" class="form-control" value="{{ $pointsPurchase->points }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Compra de Puntos</button>
    </form>
</div>
@endsection

