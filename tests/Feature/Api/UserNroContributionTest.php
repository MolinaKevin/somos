<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Nro;
use App\Models\Contribution;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated nro to view made contributions', function () {
    
    $user = User::factory()->create();

    
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    
    $contributions = Contribution::factory()->count(3)->create([
        'nro_id' => $nro->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions");

    
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($contributions as $contribution) {
        $response->assertJsonFragment(['id' => $contribution->id]);
    }
});


it('allows an authenticated nro to view a specific contribution', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $contribution = Contribution::factory()->create([
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions/{$contribution->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $contribution->id]);
});


it('prevents an nro from viewing contributions of another nro', function () {
    
    $nro1 = Nro::factory()->create();
    $nro2 = Nro::factory()->create();

    
    $user = User::factory()->create();
    $nro1->users()->attach($user->id);

    
    $contributions = Contribution::factory()->count(3)->create([
        'nro_id' => $nro2->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/nros/{$nro2->id}/contributions");

    
    $response->assertStatus(403);
});



it('allows paginating the contributions list for nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $contributions = Contribution::factory()->count(15)->create([
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions?per_page=5");

    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});

