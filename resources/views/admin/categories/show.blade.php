{{-- resources/views/admin/purchases/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Compra #{{ $purchase->id }}</h1>

    <p><strong>Comercio:</strong> {{ $purchase->commerce->name }}</p>
    <p><strong>Usuario:</strong> {{ $purchase->user->name }}</p>
    <p><strong>Monto:</strong> ${{ number_format($purchase->amount / 100, 2) }}</p>

    <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Volver a la lista</a>
</div>
@endsection

