@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Foto</h1>

    <form action="{{ route('admin.fotos.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="fotable_id">Comercio</label>
            <select name="fotable_id" id="fotable_id" class="form-control" required>
                <option value="">Seleccione un comercio</option>
                @foreach($commerces as $commerce)
                    <option value="{{ $commerce->id }}">{{ $commerce->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="fotable_type">Tipo de Fotable</label>
            <input type="text" name="fotable_type" id="fotable_type" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="path">Path de la Foto</label>
            <input type="text" name="path" id="path" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Crear Foto</button>
        <a href="{{ route('admin.fotos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

