@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Comercio</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.commerces.update', $commerce->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $commerce->name) }}" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $commerce->email) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Comercio</button>
        <a href="{{ route('admin.commerces.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

