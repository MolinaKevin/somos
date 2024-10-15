{{-- resources/views/admin/cashouts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Cashouts</h1>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.cashouts.create') }}" class="btn btn-primary mb-3">Crear Cashout</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Comercio</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashouts as $cashout)
                <tr>
                    <td>{{ $cashout->id }}</td>
                    <td>{{ $cashout->commerce->name }}</td>
                    <td>${{ $cashout->formatted_points }} puntos</td>
                    <td>
                        <a href="{{ route('admin.cashouts.show', $cashout->id) }}" class="btn btn-info">Ver</a>
                        <a href="{{ route('admin.cashouts.edit', $cashout->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('admin.cashouts.destroy', $cashout->id) }}" method="POST" style="display:inline;">
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

