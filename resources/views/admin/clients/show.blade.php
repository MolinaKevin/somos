<!-- resources/views/admin/clients/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Detalles del Cliente</h1>
        
        <div class="card">
            <div class="card-header">
                {{ $client->name }}
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> {{ $client->email }}</p>
                <!-- Otros detalles que desees mostrar -->
            </div>
        </div>
        
        <a href="{{ route('admin.clients.index') }}" class="btn btn-primary mt-3">Volver a la lista de clientes</a>
    </div>
@endsection

