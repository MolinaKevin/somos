@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Administrar Comercios</h1>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('admin.commerces.create') }}" class="btn btn-primary mb-3">Crear Comercio</a>

    @if ($commerces->isEmpty())
        <p>No hay comercios registrados.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Ciudad</th> <!-- Añadir columna Ciudad -->
                    <th>Teléfono</th> <!-- Añadir columna Teléfono -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commerces as $commerce)
                    <tr>
                        <td>{{ $commerce->name }}</td>
                        <td>{{ $commerce->email }}</td>
                        <td>{{ $commerce->city }}</td> <!-- Mostrar la ciudad -->
                        <td>{{ $commerce->phone_number }}</td> <!-- Mostrar el teléfono -->
                        <td>
                            <a href="{{ route('admin.commerces.show', $commerce->id) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('admin.commerces.edit', $commerce->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('admin.commerces.destroy', $commerce->id) }}" method="POST" style="display:inline;">
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

