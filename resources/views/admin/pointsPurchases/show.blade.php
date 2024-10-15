@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Compra de Puntos</h1>

    <ul class="list-group">
        <li class="list-group-item"><strong>ID:</strong> {{ $pointsPurchase->id }}</li>
        <li class="list-group-item"><strong>Usuario:</strong> {{ $pointsPurchase->user->name }}</li>
        <li class="list-group-item"><strong>Comercio:</strong> {{ $pointsPurchase->commerce->name }}</li>
        <li class="list-group-item"><strong>Puntos:</strong> {{ $pointsPurchase->points }}</li>
        <li class="list-group-item"><strong>UUID:</strong> {{ $pointsPurchase->uuid }}</li>
    </ul>

    <a href="{{ route('admin.pointsPurchases.index') }}" class="btn btn-secondary">Volver a la lista</a>
</div>
@endsection

