<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows admin options when authenticated', function () {
    
    $user = User::factory()->create();

    
    $this->actingAs($user);

    
    $response = $this->get('/dashboard');

    
    $response->assertStatus(200);

    
    $response->assertSee('Administrar comercios');
    $response->assertSee('Administrar instituciones');
    $response->assertSee('Administrar clientes');
    $response->assertSee('Administrar somos');
    $response->assertSee('Administrar donaciones');
    $response->assertSee('Administrar contribuciones');
    $response->assertSee('Administrar cashouts');
    $response->assertSee('Administrar compras');
    $response->assertSee('Administrar compras con puntos');
    $response->assertSee('Administrar imágenes de fondo');

    
    $this->get('/admin/commerces')->assertStatus(200);
    $this->get('/admin/nros')->assertStatus(200);
    $this->get('/admin/clients')->assertStatus(200);
    $this->get('/admin/somos')->assertStatus(200);
    $this->get('/admin/donations')->assertStatus(200);
    $this->get('/admin/contributions')->assertStatus(200);
    $this->get('/admin/cashouts')->assertStatus(200);
    $this->get('/admin/purchases')->assertStatus(200);
    $this->get('/admin/pointsPurchases')->assertStatus(200);
    $this->get('/admin/fotos')->assertStatus(200);
});

