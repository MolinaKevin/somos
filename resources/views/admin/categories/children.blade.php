{{-- resources/views/admin/categories/children.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Categorías Hijas de: {{ $category->name }}</h1>

    @if ($children->isEmpty())
        <p>No hay categorías hijas para esta categoría.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($children as $child)
                    <tr>
                        <td>{{ $child->id }}</td>
                        <td>{{ $child->name }}</td>
                        <td>{{ $child->slug }}</td>
                        <td>
                            <a href="{{ route('admin.categories.show', $child->id) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('admin.categories.edit', $child->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Volver a la lista</a>
</div>
@endsection

