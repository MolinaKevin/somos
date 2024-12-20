<?php

use App\Models\User;
use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('lang');
});


it('can see the translations list', function () {
    
    $admin = User::factory()->create();

    
    L10n::factory()->count(3)->create([
        'locale' => 'es',
        'group' => 'auth',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/l10ns');

    
    $response->assertStatus(200);

    
    L10n::all()->each(function ($l10n) use ($response) {
        $response->assertSee($l10n->key);
        $response->assertSee($l10n->value);
    });
});


it('can view a translation detail', function () {
    
    $admin = User::factory()->create();

    
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/l10ns/{$translation->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee($translation->key);
    $response->assertSee($translation->value);
});


it('can create a translation', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];

    
    $response = $this->post('/admin/l10ns', $translationData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10ns');

    
    $this->assertDatabaseHas('l10ns', [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);
});


it('can update a translation', function () {
    
    $admin = User::factory()->create();

    
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    
    $this->actingAs($admin);

    $updatedData = [
        'locale' => 'es',     
        'group' => 'auth',    
        'key' => 'login',     
        'value' => 'Iniciar sesión',  
    ];

    
    $response = $this->put("/admin/l10ns/{$translation->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10ns');

    
    $this->assertDatabaseHas('l10ns', [
        'id' => $translation->id,
        'value' => 'Iniciar sesión',
    ]);
});

it('prevents creating duplicate translations for the same key and group', function () {
    
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];


    $admin = User::factory()->create(); 

    
    $this->actingAs($admin);

    
    $response = $this->postJson('/admin/l10ns', $translationData);

    $response->assertStatus(302);
    $response = $this->postJson('/admin/l10ns', $translationData);

    
    $response->assertStatus(422);
    $this->assertDatabaseCount('l10ns', 1); 
});


it('can delete a translation', function () {
    
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    
    $response = $this->actingAs($adminUser)->delete("/admin/l10ns/{$translation->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10ns');

    
    $this->assertDatabaseMissing('l10ns', [
        'id' => $translation->id,
    ]);
});


it('validates translation data', function () {
    
    $adminUser = User::factory()->create();

    
    $this->actingAs($adminUser);

    
    $response = $this->postJson('/admin/l10ns', []);

    
    $response->assertStatus(422);

    
    $response->assertJsonValidationErrors(['locale', 'group', 'key', 'value']);
});



it('creates a JSON file for the specified locale', function () {
    
    $admin = User::factory()->create();
    $this->actingAs($admin);

    
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];

    
    $response = $this->post('/admin/l10ns', $translationData);

    
    $response->assertStatus(302);

    
    $filePath = public_path("lang/es.json");
    expect(File::exists($filePath))->toBeTrue();

    
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Acceder');

});


it('updates the JSON file when a translation is updated', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    
    $updatedData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Iniciar sesión',
    ];

    $this->put("/admin/l10ns/{$translation->id}", $updatedData);

    
    $filePath = public_path("lang/es.json");
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Iniciar sesión');

});


it('does not allow duplicate translations and does not alter the JSON file', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    
    $duplicateData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Ingresar',
    ];

    $response = $this->postJson('/admin/l10ns', $duplicateData);
    $response->assertStatus(422);

    
    $filePath = public_path("lang/es.json");
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Acceder');

});

