<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('can display the login page', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Login');
});

it('can login a user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/login', [
        'email' => 'testuser@example.com',
        'password' => 'password123'
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/login', [
        'email' => 'testuser@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

