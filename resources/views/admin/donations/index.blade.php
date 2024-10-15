@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Administrar Donaciones</h1>

    <a href="{{ route('admin.donations.create') }}" class="btn btn-primary mb-3">Crear Donación</a>

    <table class="table">
        <thead>
            <tr>
                <th>Comercio</th>
                <th>Institución (NRO)</th>
                <th>Puntos</th>
                <th>Puntos Donados</th>
                <th>Pagado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($donations as $donation)
            <tr>
                <td>{{ $donation->commerce->name }}</td>
                <td>{{ $donation->nro->name }}</td>
                <td>{{ $donation->formatted_points }} puntos</td> 
                <td>{{ $donation->formatted_donated_points }} puntos donados</td> 
                <td>{{ $donation->is_paid ? 'Sí' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.donations.show', $donation->id) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('admin.donations.edit', $donation->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('admin.donations.destroy', $donation->id) }}" method="POST" style="display:inline;">
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

