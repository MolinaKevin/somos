{{-- resources/views/admin/l10n/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Crear Nueva Traducción</h1>

    <form action="{{ route('admin.l10n.store') }}" method="POST">
        @csrf
        <div>
            <label for="locale">Idioma</label>
            <input type="text" name="locale" id="locale" value="{{ old('locale') }}" required>
        </div>

        <div>
            <label for="group">Grupo</label>
            <input type="text" name="group" id="group" value="{{ old('group') }}" required>
        </div>

        <div>
            <label for="key">Clave</label>
            <input type="text" name="key" id="key" value="{{ old('key') }}" required>
        </div>

        <div>
            <label for="value">Valor</label>
            <textarea name="value" id="value" required>{{ old('value') }}</textarea>
        </div>

        <button type="submit">Guardar Traducción</button>
    </form>
@endsection

