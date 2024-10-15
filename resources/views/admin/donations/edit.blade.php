@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Donación</h1>

    <form action="{{ route('admin.donations.update', $donation->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="commerce_id">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-control">
                @foreach($commerces as $commerce)
                    <option value="{{ $commerce->id }}" {{ $donation->commerce_id == $commerce->id ? 'selected' : '' }}>
                        {{ $commerce->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nro_id">Institución (NRO)</label>
            <select name="nro_id" id="nro_id" class="form-control">
                @foreach($nros as $nro)
                    <option value="{{ $nro->id }}" {{ $donation->nro_id == $nro->id ? 'selected' : '' }}>
                        {{ $nro->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="points">Puntos</label>
            <input type="number" name="points" id="points" class="form-control" value="{{ $donation->formatted_points }}" required>
        </div>

        <div class="form-group">
            <label for="donated_points">Puntos Donados</label>
            <input type="number" name="donated_points" id="donated_points" class="form-control" value="{{ $donation->formatted_donated_points }}" required>
        </div>

        <div class="form-group">
            <label for="is_paid">Pagado</label>
            <select name="is_paid" id="is_paid" class="form-control">
                <option value="0" {{ !$donation->is_paid ? 'selected' : '' }}>No</option>
                <option value="1" {{ $donation->is_paid ? 'selected' : '' }}>Sí</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Donación</button>
    </form>
</div>
@endsection

