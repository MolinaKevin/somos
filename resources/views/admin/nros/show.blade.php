@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Detalles de la Institución</h1>

        <p><strong>Nombre:</strong> {{ $nro->name }}</p>
        <p><strong>Correo Electrónico:</strong> {{ $nro->email }}</p>

        <a href="{{ route('nros.index') }}" class="btn btn-secondary">Volver</a>
    </div>
@endsection

