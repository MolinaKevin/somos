@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Categorías</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Crear Categoría</a>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Slug</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
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

