@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Administrar Instituciones</h1>

        <a href="{{ route('admin.nros.create') }}" class="btn btn-primary mb-3">Crear Institución</a>

        @if($nros->isEmpty())
            <p>No hay instituciones registradas.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nros as $nro)
                        <tr>
                            <td>{{ $nro->name }}</td>
                            <td>{{ $nro->email }}</td>
                            <td>
                                <a href="{{ route('admin.nros.show', $nro->id) }}" class="btn btn-info btn-sm">Ver</a>
                                <a href="{{ route('admin.nros.edit', $nro->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                <form action="{{ route('admin.nros.destroy', $nro->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

