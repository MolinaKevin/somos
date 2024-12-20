<?php

use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see donations list', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    
    Donation::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 100,
        'donated_points' => 50,
        'is_paid' => true,
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/donations');

    
    $response->assertStatus(200);

    
    $response->assertSee('100 puntos');
    $response->assertSee('50 puntos donados');
});


it('can create a donation', function () {
    
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    
    $this->actingAs($admin);

    
    $donationData = [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ];

    
    $response = $this->post('/admin/donations', $donationData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    
    $this->assertDatabaseHas('donations', [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);
});


it('can view a donation detail', function () {
    
    $admin = User::factory()->create();
    
    
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/donations/{$donation->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('200 puntos');
    $response->assertSee('100 puntos donados');
});


it('can update a donation', function () {
    
    $admin = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 100,
        'donated_points' => 50,
        'is_paid' => true,
    ]);

    
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'nro_id' => $nro->id,           
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => false,
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/donations/{$donation->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    
    $this->assertDatabaseHas('donations', [
        'id' => $donation->id,
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => false,
    ]);
});



it('can delete a donation', function () {
    
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);

    
    $adminUser = User::factory()->create();

    
    $response = $this->actingAs($adminUser)->delete("/admin/donations/{$donation->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    
    $this->assertDatabaseMissing('donations', [
        'id' => $donation->id,
    ]);
});

