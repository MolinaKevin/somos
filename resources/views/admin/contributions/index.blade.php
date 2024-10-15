@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Administrar Contribuciones</h1>

    <a href="{{ route('admin.contributions.create') }}" class="btn btn-primary mb-3">Crear Contribución</a>

    <table class="table">
        <thead>
            <tr>
                <th>Somos</th>
                <th>Institución (NRO)</th>
                <th>Puntos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contributions as $contribution)
                <tr>
                    <td>{{ $contribution->somos->name }}</td>
                    <td>{{ $contribution->nro->name }}</td>
                    <td>{{ $contribution->points }} puntos</td>
                    <td>
                        <a href="{{ route('admin.contributions.show', $contribution->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('admin.contributions.edit', $contribution->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('admin.contributions.destroy', $contribution->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

