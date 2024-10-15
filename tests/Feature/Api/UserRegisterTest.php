<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

uses(RefreshDatabase::class);

it('registers a new user through the API', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // The API call
    $response = $this->postJson('/register', $userData);

    // Assert the response
    $response->assertStatus(201);

    // Assert the user was created
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);

    // Assert the password was hashed correctly
    $this->assertTrue(Hash::check('password', User::first()->password));
});

it('register dont work with short password', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass',
    ];

    // The API call
    $response = $this->postJson('/register', $userData);

    // Assert the response
    $response->assertStatus(422);

    // Assert the user was created
    $this->assertDatabaseMissing('users', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);
});

it('register dont work with two different password', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'pass',
        'password_confirmation' => 'password',
    ];

    // The API call
    $response = $this->postJson('/register', $userData);

    // Assert the response
    $response->assertStatus(422);

    // Assert the user was created
    $this->assertDatabaseMissing('users', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);
});
