@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Editar Cliente</h1>
        <form action="{{ url('/admin/clients/' . $client->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $client->name }}" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $client->email }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection

