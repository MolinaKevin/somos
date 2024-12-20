<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Somos;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(RefreshDatabase::class);

it('allows an authenticated user to view a specific somos', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $somos = Somos::factory()->create();

    
    $response = $this->getJson("/api/somos/{$somos->id}");
    
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $somos->id]);
});


it('allows an authenticated user to update a somos', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $somos = Somos::factory()->create([
        'name' => 'Nombre Original',
        'description' => 'Descripción Original',
    ]);

    
    $updatedData = [
        'name' => 'Somos Actualizado',
        'description' => 'Descripción actualizada',
    ];

    
    $response = $this->putJson("/api/somos/{$somos->id}", $updatedData);

    
    $response->assertStatus(200);
    
    
    $this->assertDatabaseHas('somos', [
        'id' => $somos->id,
        'name' => 'Somos Actualizado',
        'description' => 'Descripción actualizada',
    ]);
});



it('allows an authenticated user to delete a somos', function () {
    
    $somos = Somos::factory()->create();

    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->deleteJson("/api/somos/{$somos->id}");

    
    $response->assertStatus(204);
    $this->assertDatabaseMissing('somos', ['id' => $somos->id]);
});


it('allows paginating the somos list', function () {
    
    $somosList = Somos::factory()->count(20)->create();

    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/somos?per_page=10');

    //dd($response);

    
    $response->assertStatus(200);
    $response->assertJsonCount(10, 'data');
});


