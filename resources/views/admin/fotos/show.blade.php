@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Foto</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ID: {{ $foto->id }}</h5>
            <p class="card-text"><strong>Path:</strong> {{ $foto->path }}</p>
            <p class="card-text"><strong>Tipo de Fotable:</strong> {{ $foto->fotable_type }}</p>
            <p class="card-text"><strong>Fotable ID:</strong> {{ $foto->fotable_id }}</p>
        </div>
    </div>

    <a href="{{ route('admin.fotos.index') }}" class="btn btn-primary mt-3">Volver a la lista</a>
</div>
@endsection

