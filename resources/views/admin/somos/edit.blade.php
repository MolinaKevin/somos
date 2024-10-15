@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Somos</h1>

    <form action="{{ route('admin.somos.update', $somos->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $somos->name) }}" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $somos->email) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Somos</button>
        <a href="{{ route('admin.somos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

