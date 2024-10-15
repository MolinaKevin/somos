@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Administrar Somos</h1>

    <a href="{{ route('admin.somos.create') }}" class="btn btn-primary mb-3">Crear Somos</a>

    @if ($somos->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($somos as $somosItem)
                    <tr>
                        <td>{{ $somosItem->name }}</td>
                        <td>{{ $somosItem->email }}</td>
                        <td>
                            <a href="{{ route('admin.somos.show', $somosItem->id) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('admin.somos.edit', $somosItem->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('admin.somos.destroy', $somosItem->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay registros de Somos disponibles.</p>
    @endif
</div>
@endsection

