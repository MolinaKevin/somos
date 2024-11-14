<?php

use App\Models\User;
use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Ver listado de traducciones
it('can see the translations list', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear algunas traducciones
    L10n::factory()->count(3)->create([
        'locale' => 'es',
        'group' => 'auth',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de lista de traducciones
    $response = $this->get('/admin/l10n');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el listado de traducciones está presente
    L10n::all()->each(function ($l10n) use ($response) {
        $response->assertSee($l10n->key);
        $response->assertSee($l10n->value);
    });
});

// Test: Ver detalle de una traducción
it('can view a translation detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear una traducción
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la traducción
    $response = $this->get("/admin/l10n/{$translation->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la traducción están presentes
    $response->assertSee($translation->key);
    $response->assertSee($translation->value);
});

// Test: Crear una traducción
it('can create a translation', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de la traducción
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];

    // Enviar solicitud para crear una nueva traducción
    $response = $this->post('/admin/l10n', $translationData);

    // Verificar que la respuesta sea un redirect a la lista de traducciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10n');

    // Verificar que la traducción fue creada en la base de datos
    $this->assertDatabaseHas('l10ns', [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);
});

// Test: Actualizar una traducción
it('can update a translation', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear una traducción
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    $updatedData = [
        'locale' => 'es',     // Idioma
        'group' => 'auth',    // Grupo de traducción
        'key' => 'login',     // Clave de traducción
        'value' => 'Iniciar sesión',  // Asegúrate de que este valor sea correcto
    ];

    // Enviar solicitud para actualizar la traducción
    $response = $this->put("/admin/l10n/{$translation->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de traducciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10n');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('l10ns', [
        'id' => $translation->id,
        'value' => 'Iniciar sesión',
    ]);
});

it('prevents creating duplicate translations for the same key and group', function () {
    // Intentar crear la misma traducción dos veces
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];


    $admin = User::factory()->create(); // Crear un usuario admin

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Intentar crear la traducción duplicada
    $response = $this->postJson('/admin/l10n', $translationData);

    $response->assertStatus(302);
    $response = $this->postJson('/admin/l10n', $translationData);

    // Verificar que se recibe un error de duplicado
    $response->assertStatus(422);
    $this->assertDatabaseCount('l10ns', 1); // Solo debe existir 1 registro
});

// Test: Eliminar una traducción
it('can delete a translation', function () {
    // Crear una traducción
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Actuar como el usuario autenticado (por ejemplo, un administrador)
    $response = $this->actingAs($adminUser)->delete("/admin/l10n/{$translation->id}");

    // Verificar que la respuesta sea un redirect a la lista de traducciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/l10n');

    // Verificar que la traducción fue eliminada de la base de datos
    $this->assertDatabaseMissing('l10ns', [
        'id' => $translation->id,
    ]);
});

// Test: Validación de datos para la creación de traducciones
it('validates translation data', function () {
    // Crear un usuario administrador o autenticado
    $adminUser = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($adminUser);

    // Intentar crear una traducción sin datos válidos usando una solicitud JSON
    $response = $this->postJson('/admin/l10n', []);

    // Verificar que la respuesta sea un error 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Verificar que se muestran los mensajes de validación
    $response->assertJsonValidationErrors(['locale', 'group', 'key', 'value']);
});

use Illuminate\Support\Facades\File;

// Test: Verificar que se crea el archivo JSON al crear una traducción
it('creates a JSON file for the specified locale', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Crear una nueva traducción
    $translationData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ];

    // Enviar la solicitud de creación
    $response = $this->post('/admin/l10n', $translationData);

    // Verificar que se redirige correctamente
    $response->assertStatus(302);

    // Verificar que el archivo es.json se haya creado en public/lang
    $filePath = public_path("lang/es.json");
    expect(File::exists($filePath))->toBeTrue();

    // Verificar el contenido del archivo JSON
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Acceder');

    // Limpiar después del test
    File::delete($filePath);
});

// Test: Verificar que se actualiza el archivo JSON al actualizar una traducción
it('updates the JSON file when a translation is updated', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Crear una traducción inicial
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    // Actualizar la traducción
    $updatedData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Iniciar sesión',
    ];

    $this->put("/admin/l10n/{$translation->id}", $updatedData);

    // Verificar que el archivo es.json se haya actualizado
    $filePath = public_path("lang/es.json");
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Iniciar sesión');

    // Limpiar después del test
    File::delete($filePath);
});

// Test: Verificar que no se permite duplicar traducciones y el archivo JSON sigue igual
it('does not allow duplicate translations and does not alter the JSON file', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Crear una traducción inicial
    $translation = L10n::factory()->create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Acceder',
    ]);

    // Intentar crear un duplicado
    $duplicateData = [
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Ingresar',
    ];

    $response = $this->postJson('/admin/l10n', $duplicateData);
    $response->assertStatus(422);

    // Verificar que el contenido del archivo JSON no ha cambiado
    $filePath = public_path("lang/es.json");
    $translations = json_decode(File::get($filePath), true);
    expect($translations['auth']['login'])->toBe('Acceder');

    // Limpiar después del test
    File::delete($filePath);
});

