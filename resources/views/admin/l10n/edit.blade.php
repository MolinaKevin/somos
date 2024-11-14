{{-- resources/views/admin/l10n/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Editar Traducción</h1>

    <form action="{{ route('admin.l10n.update', $translation->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="locale">Idioma</label>
            <input type="text" name="locale" id="locale" value="{{ old('locale', $translation->locale) }}" required>
        </div>

        <div>
            <label for="group">Grupo</label>
            <input type="text" name="group" id="group" value="{{ old('group', $translation->group) }}" required>
        </div>

        <div>
            <label for="key">Clave</label>
            <input type="text" name="key" id="key" value="{{ old('key', $translation->key) }}" required>
        </div>

        <div>
            <label for="value">Valor</label>
            <textarea name="value" id="value" required>{{ old('value', $translation->value) }}</textarea>
        </div>

        <button type="submit">Actualizar Traducción</button>
    </form>


@endsection

