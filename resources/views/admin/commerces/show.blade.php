@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Comercio</h1>

    <div class="card">
        <div class="card-header">
            {{ $commerce->name }}
        </div>
        <div class="card-body">
            <p><strong>Correo Electrónico:</strong> {{ $commerce->email }}</p>
            <p><strong>Número de Teléfono:</strong> {{ $commerce->phone_number }}</p> <!-- Agregar el número de teléfono -->
            <p><strong>Ciudad:</strong> {{ $commerce->city }}</p> <!-- Agregar la ciudad -->
            <a href="{{ route('admin.commerces.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>

</div>
@endsection

