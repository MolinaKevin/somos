<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to register', function () {
    
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password', 
        'password_confirmation' => 'password', 
    ];

    
    $response = $this->post('/register', $userData);

    
    $response->assertStatus(302); 
    $response->assertRedirect('/dashboard');

    
    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
    ]);

});
