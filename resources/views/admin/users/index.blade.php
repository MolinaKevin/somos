@extends('layouts.app')

@section('content')
    <h1>Administrar clientes</h1>
    <ul>
        @foreach($users as $user)
            <li>{{ $user->name }}</li>
        @endforeach
    </ul>
@endsection

