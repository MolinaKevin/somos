@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Compras de Puntos</h1>
    
    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('admin.pointsPurchases.create') }}" class="btn btn-primary">Nueva Compra de Puntos</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Comercio</th>
                <th>Usuario</th>
                <th>Puntos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pointsPurchases as $pointsPurchase)
                <tr>
                    <td>{{ $pointsPurchase->id }}</td>
                    <td>{{ $pointsPurchase->commerce->name }}</td>
                    <td>{{ $pointsPurchase->user ? $pointsPurchase->user->name : 'Sin usuario' }}</td> {{-- Verificación aquí --}}
                    <td>{{ $pointsPurchase->points }}</td>
                    <td>
                        <a href="{{ route('admin.pointsPurchases.show', $pointsPurchase->id) }}" class="btn btn-info">Ver</a>
                        <a href="{{ route('admin.pointsPurchases.edit', $pointsPurchase->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('admin.pointsPurchases.destroy', $pointsPurchase->id) }}" method="POST" style="display:inline;">
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

