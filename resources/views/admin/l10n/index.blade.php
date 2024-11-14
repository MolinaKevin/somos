{{-- resources/views/admin/l10n/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Lista de Traducciones</h1>
    <table>
        <thead>
            <tr>
                <th>Idioma</th>
                <th>Grupo</th>
                <th>Clave</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($translations as $translation)
                <tr>
                    <td>{{ $translation->locale }}</td>
                    <td>{{ $translation->group }}</td>
                    <td>{{ $translation->key }}</td>
                    <td>{{ $translation->value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

