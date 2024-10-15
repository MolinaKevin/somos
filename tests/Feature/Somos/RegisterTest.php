<?php

use App\Models\User;
use App\Models\Role; 
use Laravel\Fortify\Features;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('can view the registration page', function () {
    $response = get('/register');
    
    $response->assertOk();
});

it('can register a new user', function () {
	$time = time();
    $userData = [
        'name' => 'Test User',
        'pass' => 'DE-111111111',
        'email' => 'test'.$time.'@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = post('/register', $userData);

    $response->assertRedirect('/dashboard'); // Suponiendo que rediriges a '/home' tras el registro

    // Asegura que el usuario fue creado y está autenticado
    assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test'.$time.'@example.com',
		'points' => 0
    ]);
});

it('cannot register a user with invalid data', function () {
    $userData = [
        'name' => '',
        'email' => 'not-an-email',
        'password' => '123',
        'password_confirmation' => '1234',
    ];

    $response = post('/register', $userData);

    $response->assertSessionHasErrors(['name', 'email', 'password']);

    $this->assertDatabaseMissing('users', [
        'email' => 'not-an-email',
    ]);
});
