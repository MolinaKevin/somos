{{-- resources/views/admin/cashouts/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Cashout #{{ $cashout->id }}</h1>

    <p><strong>Comercio:</strong> {{ $cashout->commerce->name }}</p>
    <p><strong>Monto:</strong> ${{ $cashout->formatted_points }} puntos</p>

    <a href="{{ route('admin.cashouts.index') }}" class="btn btn-secondary">Volver a la lista</a>
</div>
@endsection

