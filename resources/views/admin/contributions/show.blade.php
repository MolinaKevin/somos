@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Contribución</h1>

    <div class="card">
        <div class="card-header">
            Contribución de {{ $contribution->somos->name }} a {{ $contribution->nro->name }}
        </div>
        <div class="card-body">
            <p><strong>Puntos:</strong> {{ $contribution->points }} puntos</p>
            <p><strong>Creado el:</strong> {{ $contribution->created_at->format('d/m/Y H:i') }}</p>
            <a href="{{ route('admin.contributions.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection

