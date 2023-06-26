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



it('can create users with different roles', function () {
    if (! Features::enabled(Features::registration())) {
        return $this->markTestSkipped('Registration is not enabled.');
    }

    // Crea un usuario con el rol de cliente.
    $user1 = User::factory()->create([
        'name' => 'Test User 1',
        'email' => 'test1@example.com',
        'password' => bcrypt('password'),
    ]);

	$role1 = Role::factory()->create([
		'name' => 'cliente'
	]);

    $role1 = Role::where('name', 'cliente')->first();
    $user1->roles()->attach($role1->id);

    // Crea un usuario con el rol de comercio.
    $user2 = User::factory()->create([
        'name' => 'Test User 2',
        'email' => 'test2@example.com',
        'password' => bcrypt('password'),
    ]);

	$role2 = Role::factory()->create([
		'name' => 'comercio'
	]);

    $role2 = Role::where('name', 'comercio')->first();
    $user2->roles()->attach($role2->id);

    // Crea un usuario con el rol de nro.
    $user3 = User::factory()->create([
        'name' => 'Test User 3',
        'email' => 'test3@example.com',
        'password' => bcrypt('password'),
    ]);

	$role3 = Role::factory()->create([
		'name' => 'nro'
	]);

    $role3 = Role::where('name', 'nro')->first();
    $user3->roles()->attach($role3->id);

    // Comprueba que los usuarios tienen los roles correctos.
    $this->assertTrue($user1->roles->contains($role1));
    $this->assertTrue($user2->roles->contains($role2));
    $this->assertTrue($user3->roles->contains($role3));
});

