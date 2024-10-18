<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Asegúrate de que la base de datos se reinicie entre pruebas
    $this->refreshDatabase();
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
});

it('creates a new user through the API', function () {
    // Crear un usuario existente que será el referenciador
    User::create([
        'name' => 'Referrer User',
        'email' => 'referrer@example.com',
        'password' => Hash::make('password'),
        'language' => 'en',
        'pass' => 'KK-36779437', // Este será el pass referenciado
    ]);

    // Crear el nuevo usuario
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => 'en',
        'pass' => 'TT-36779437', // Puede ser un nuevo pass
        'referrer_pass' => 'KK-36779437', // Referenciando el pass del usuario existente
        'current_team_id' => 1,
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
        'name' => 'Test User',
        'language' => 'en',
    ]);
});



it('fails to create a user with invalid data', function () {
    $userData = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'language' => 'long_language_code', // Invalid length
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(422);
});

it('lists all users', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200);
});

it('returns a single user', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'language' => $user->language,
    ]);
});

it('fails to return a non-existing user', function () {
    $response = $this->getJson('/api/users/99999');

    $response->assertStatus(404);
});

it('updates an existing user', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => 'Updated User',
        'email' => 'updated@example.com',
        'language' => 'es',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated User',
        'email' => 'updated@example.com',
        'language' => 'es',
    ]);
});

it('fails to update a user with invalid data', function () {
    $user = User::factory()->create();

    $updateData = [
        'email' => 'invalid-email',
        'language' => 'long_language_code', // Invalid length
    ];

    $response = $this->putJson("/api/users/{$user->id}", $updateData);

    $response->assertStatus(422);
});

it('deletes an existing user', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('fails to delete a non-existing user', function () {
    $response = $this->deleteJson('/api/users/99999');

    $response->assertStatus(404);
});

