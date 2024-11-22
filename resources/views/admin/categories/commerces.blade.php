{{-- resources/views/admin/categories/commerces.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Comercios Asociados a la Categoría: {{ $category->name }}</h1>

    @if ($commerces->isEmpty())
        <p>No hay comercios asociados a esta categoría.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commerces as $commerce)
                    <tr>
                        <td>{{ $commerce->id }}</td>
                        <td>{{ $commerce->name }}</td>
                        <td>{{ $commerce->address }}</td>
                        <td>
                            <a href="{{ route('admin.commerces.show', $commerce->id) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('admin.commerces.edit', $commerce->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Volver a la lista</a>
</div>
@endsection

