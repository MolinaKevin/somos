<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to register', function () {
    // Definir los datos del usuario que se registrará
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password', // La contraseña plana
        'password_confirmation' => 'password', // Confirmación de contraseña
    ];

    // Realizar la solicitud de registro
    $response = $this->post('/register', $userData);

    // Verificar que el registro fue exitoso
    $response->assertStatus(302); // 302 para redirección
    $response->assertRedirect('/dashboard');

    // Verificar que el usuario fue creado en la base de datos
    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
    ]);

});
