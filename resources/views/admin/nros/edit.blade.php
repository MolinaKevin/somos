@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Editar Institución</h1>

        <form action="{{ route('nros.update', $nro->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $nro->name) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $nro->email) }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Institución</button>
            <a href="{{ route('nros.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection

