<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a new user through the API', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => 'en',
    ];

    $response = $this->postJson('/api/register', $userData);

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
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422); // Unprocessable Entity

    $response->assertJsonStructure(['name', 'email', 'password']);
    $this->assertArrayHasKey('name', $response->json());
    $this->assertArrayHasKey('email', $response->json());
    $this->assertArrayHasKey('password', $response->json());
});

it('logs in an existing user through the API', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
    ]);

    $loginData = [
        'email' => 'testuser@example.com',
        'password' => 'password',
    ];

    $response = $this->postJson('/api/login', $loginData);

    $response->assertStatus(200);
    $this->assertNotNull($response['user']);
    $this->assertNotNull($response['token']);
});

it('returns the profile of the authenticated user', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200);
    $this->assertEquals($user->id, $response['id']);
    $this->assertEquals($user->name, $response['name']);
    $this->assertEquals($user->email, $response['email']);
    $this->assertEquals('en', $response['language']); // Verificar idioma por defecto
});

it('updates the user information', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $updateData = [
        'name' => 'Updated User',
        'language' => 'es',
    ];

    $response = $this->patchJson('/api/users/' . $user->id, $updateData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated User',
        'language' => 'es',
    ]);
});

it('fails to update user information with invalid data', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $updateData = [
        'name' => '',
    ];

    $response = $this->patchJson('/api/users/' . $user->id, $updateData);

    $response->assertStatus(422); // Unprocessable Entity
});

it('deletes a user', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson('/api/users/' . $user->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('fails to delete a non-existent user', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson('/api/users/' . 999); // ID que no existe

    $response->assertStatus(404); // Not Found
});

