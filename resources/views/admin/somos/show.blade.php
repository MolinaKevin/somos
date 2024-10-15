@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de Somos</h1>

    <div class="card">
        <div class="card-header">
            <strong>Nombre:</strong> {{ $somos->name }}
        </div>
        <div class="card-body">
            <p><strong>Correo Electrónico:</strong> {{ $somos->email }}</p>
        </div>
    </div>

    <a href="{{ route('admin.somos.index') }}" class="btn btn-secondary mt-3">Volver a la lista</a>
</div>
@endsection

