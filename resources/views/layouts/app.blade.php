<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])

</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li><a href="{{ route('logout') }}">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    @livewireScripts
    <footer>
        <p>© 2024 Administración del Sistema</p>
    </footer>

</body>
</html>

