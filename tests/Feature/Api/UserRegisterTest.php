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

    
    $response = $this->postJson('/register', $userData);

    
    $response->assertStatus(201);

    
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);

    
    $this->assertTrue(Hash::check('password', User::first()->password));
});

it('register dont work with short password', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass',
    ];

    
    $response = $this->postJson('/register', $userData);

    
    $response->assertStatus(422);

    
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

    
    $response = $this->postJson('/register', $userData);

    
    $response->assertStatus(422);

    
    $this->assertDatabaseMissing('users', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);
});
