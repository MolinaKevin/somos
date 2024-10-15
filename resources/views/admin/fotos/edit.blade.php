@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Foto</h1>

    <form action="{{ route('admin.fotos.update', $foto->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="fotable_id">Comercio</label>
            <select name="fotable_id" id="fotable_id" class="form-control" required>
                @foreach($commerces as $commerce)
                    <option value="{{ $commerce->id }}" {{ $foto->fotable_id == $commerce->id ? 'selected' : '' }}>
                        {{ $commerce->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="fotable_type">Tipo de Fotable</label>
            <input type="text" name="fotable_type" id="fotable_type" class="form-control" value="{{ $foto->fotable_type }}" required>
        </div>

        <div class="form-group">
            <label for="path">Path de la Foto</label>
            <input type="text" name="path" id="path" class="form-control" value="{{ $foto->path }}" required>
        </div>

        <button type="submit" class="btn btn-success">Actualizar Foto</button>
        <a href="{{ route('admin.fotos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

