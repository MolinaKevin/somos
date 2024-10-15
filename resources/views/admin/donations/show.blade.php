@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Donación</h1>

    <div class="card">
        <div class="card-header">
            Donación de {{ $donation->commerce->name }} a {{ $donation->nro->name }}
        </div>
        <div class="card-body">
            <p><strong>Puntos:</strong> {{ $donation->formatted_points }} puntos</p>
            <p><strong>Puntos Donados:</strong> {{ $donation->formatted_donated_points }} puntos donados</p>
            <p><strong>Pagado:</strong> {{ $donation->is_paid ? 'Sí' : 'No' }}</p>
            <p><strong>Creado el:</strong> {{ $donation->created_at->format('d/m/Y H:i') }}</p>
            <a href="{{ route('admin.donations.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection

