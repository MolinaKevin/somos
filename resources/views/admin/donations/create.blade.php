@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Donación</h1>

    <form action="{{ route('admin.donations.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="commerce_id">Comercio</label>
            <select name="commerce_id" id="commerce_id" class="form-control">
                @foreach($commerces as $commerce)
                    <option value="{{ $commerce->id }}">{{ $commerce->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nro_id">Institución (NRO)</label>
            <select name="nro_id" id="nro_id" class="form-control">
                @foreach($nros as $nro)
                    <option value="{{ $nro->id }}">{{ $nro->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="points">Puntos Donados</label>
            <input type="number" name="points" id="points" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="donated_points">Puntos a Donar</label>
            <input type="number" name="donated_points" id="donated_points" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="is_paid">Pagado</label>
            <select name="is_paid" id="is_paid" class="form-control">
                <option value="0">No</option>
                <option value="1">Sí</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Crear Donación</button>
    </form>
</div>
@endsection

