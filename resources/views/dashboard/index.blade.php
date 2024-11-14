@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Panel de Administración</h1>

        <ul class="admin-list">
            <li><a href="{{ route('admin.commerces.index') }}">Administrar comercios</a></li>
            <li><a href="{{ route('admin.nros.index') }}">Administrar instituciones</a></li>
            <li><a href="{{ route('admin.clients.index') }}">Administrar clientes</a></li>
            <li><a href="{{ route('admin.somos.index') }}">Administrar somos</a></li>
            <li><a href="{{ route('admin.donations.index') }}">Administrar donaciones</a></li>
            <li><a href="{{ route('admin.cashouts.index') }}">Administrar cashouts</a></li>
            <li><a href="{{ route('admin.contributions.index') }}">Administrar contribuciones</a></li>
            <li><a href="{{ route('admin.purchases.index') }}">Administrar compras</a></li>
            <li><a href="{{ route('admin.pointsPurchases.index') }}">Administrar compras con puntos</a></li>
            <li><a href="{{ route('admin.fotos.index') }}">Administrar imágenes de fondo</a></li>
            <li><a href="{{ route('admin.l10n.index') }}">Administrar traducciones</a></li>
        </ul>
    </div>
@endsection
