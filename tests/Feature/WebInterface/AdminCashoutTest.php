<?php

use App\Models\Cashout;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see cashouts list', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();

    
    Cashout::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'points' => 100,
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/cashouts');

    
    $response->assertStatus(200);

    
    $response->assertSee('100 puntos');
});


it('can create a cashout', function () {
    
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $this->actingAs($admin);

    
    $cashoutData = [
        'commerce_id' => $commerce->id,
        'points' => 200,
    ];

    
    $response = $this->post('/admin/cashouts', $cashoutData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    
    $this->assertDatabaseHas('cashouts', [
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);
});


it('can view a cashout detail', function () {
    
    $admin = User::factory()->create();
    
    
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/cashouts/{$cashout->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('200 puntos');
});


it('can update a cashout', function () {
    
    $admin = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 100,
    ]);

    
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'points' => 200,
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/cashouts/{$cashout->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    
    $this->assertDatabaseHas('cashouts', [
        'id' => $cashout->id,
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);
});


it('can delete a cashout', function () {
    
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);

    
    $adminUser = User::factory()->create();

    
    $response = $this->actingAs($adminUser)->delete("/admin/cashouts/{$cashout->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    
    $this->assertDatabaseMissing('cashouts', [
        'id' => $cashout->id,
    ]);
});

