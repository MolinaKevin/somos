@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Contribución</h1>

    <form action="{{ route('admin.contributions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="somos_id">Somos</label>
            <select name="somos_id" id="somos_id" class="form-control" required>
                @foreach($somes as $somos)
                    <option value="{{ $somos->id }}">{{ $somos->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nro_id">Institución (NRO)</label>
            <select name="nro_id" id="nro_id" class="form-control" required>
                @foreach($nros as $nro)
                    <option value="{{ $nro->id }}">{{ $nro->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="points">Puntos</label>
            <input type="number" name="points" id="points" class="form-control" required min="0" step="0.001">
        </div>

        <button type="submit" class="btn btn-primary">Crear Contribución</button>
    </form>
</div>
@endsection

