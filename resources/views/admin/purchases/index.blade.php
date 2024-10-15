{{-- resources/views/admin/purchases/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Compras</h1>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary mb-3">Crear Compra</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Comercio</th>
                <th>Usuario</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->id }}</td>
                    <td>{{ $purchase->commerce->name }}</td>
                    <td>{{ $purchase->user ? $purchase->user->name : 'Sin usuario' }}</td> {{-- Verificación aquí --}}
                    <td>${{ number_format($purchase->amount / 100, 2) }}</td>
                    <td>
                        <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-info">Ver</a>
                        <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

