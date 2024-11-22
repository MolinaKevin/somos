{{-- resources/views/admin/l10n/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Detalles de la Traducción</h1>

    <table>
        <tr>
            <th>Idioma</th>
            <td>{{ $translation->locale }}</td>
        </tr>
        <tr>
            <th>Grupo</th>
            <td>{{ $translation->group }}</td>
        </tr>
        <tr>
            <th>Clave</th>
            <td>{{ $translation->key }}</td>
        </tr>
        <tr>
            <th>Valor</th>
            <td>{{ $translation->value }}</td>
        </tr>
    </table>

    <a href="{{ route('admin.l10ns.edit', $translation->id) }}">Editar</a>
    <form action="{{ route('admin.l10ns.destroy', $translation->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit">Eliminar</button>
    </form>
@endsection

