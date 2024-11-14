<?php

use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear algunas traducciones de ejemplo en diferentes grupos
    L10n::create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'login',
        'value' => 'Iniciar sesión',
    ]);

    L10n::create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'logout',
        'value' => 'Cerrar sesión',
    ]);

    L10n::create([
        'locale' => 'es',
        'group' => 'language',
        'key' => 'selectLanguage',
        'value' => 'Seleccionar idioma',
    ]);
});

// Test 1: Verifica que las traducciones para un idioma y grupo se pueden obtener correctamente
it('retrieves translations for a given locale and group', function () {
    // Obtener las traducciones para el idioma 'es' y el grupo 'auth'
    $translations = L10n::where('locale', 'es')->where('group', 'auth')->get();

    // Verificar que se devuelven las traducciones correctas
    $this->assertEquals('Iniciar sesión', $translations->firstWhere('key', 'login')->value);
    $this->assertEquals('Cerrar sesión', $translations->firstWhere('key', 'logout')->value);
});

// Test 2: Verifica que se puede crear una nueva traducción en un grupo
it('creates a new translation in a group', function () {
    // Crear nueva traducción
    $newTranslation = L10n::create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'register',
        'value' => 'Registrar',
    ]);

    // Verificar que la traducción se guardó correctamente
    $this->assertDatabaseHas('l10ns', [
        'key' => 'register',
        'value' => 'Registrar',
    ]);
});

// Test 3: Verifica que se puede actualizar una traducción existente
it('updates an existing translation', function () {
    // Actualizar la traducción 'logout' en el grupo 'auth'
    $translation = L10n::where('key', 'logout')->where('group', 'auth')->first();
    $translation->update(['value' => 'Salir']);

    // Verificar que el valor de la traducción se actualizó correctamente
    $this->assertDatabaseHas('l10ns', [
        'key' => 'logout',
        'value' => 'Salir',
    ]);
});

// Test 4: Verifica que se puede eliminar una traducción
it('deletes a translation', function () {
    // Eliminar la traducción 'login' del grupo 'auth'
    $translation = L10n::where('key', 'login')->where('group', 'auth')->first();
    $translation->delete();

    // Verificar que la traducción fue eliminada
    $this->assertDatabaseMissing('l10ns', [
        'key' => 'login',
    ]);
});


// Test 6: Verifica que el modelo L10n tiene los atributos correctos
it('has the correct attributes', function () {
    // Crear una traducción
    $translation = L10n::create([
        'locale' => 'en',
        'group' => 'auth',
        'key' => 'greeting',
        'value' => 'Hello',
    ]);

    // Verificar los atributos del modelo
    $this->assertEquals('en', $translation->locale);
    $this->assertEquals('auth', $translation->group);
    $this->assertEquals('greeting', $translation->key);
    $this->assertEquals('Hello', $translation->value);
});

// Test 7: Verifica que se puede obtener todas las traducciones por grupo
it('retrieves all translations by group', function () {
    // Obtener todas las traducciones del grupo 'auth'
    $translations = L10n::where('group', 'auth')->get();

    // Verificar que se devuelven las traducciones correctas
    $this->assertEquals(2, $translations->count()); // 'login' y 'logout'
});

// Test 8: Verifica que se devuelve el recurso de traducción correctamente
it('retrieves a single translation', function () {
    // Obtener una traducción específica
    $translation = L10n::where('key', 'login')->where('group', 'auth')->first();

    // Verificar que la traducción devuelta es la correcta
    $this->assertEquals('Iniciar sesión', $translation->value);
});

// Test 9: Verifica que las traducciones están correctamente agrupadas por idioma y grupo
it('retrieves translations grouped by locale and group', function () {
    // Obtener todas las traducciones agrupadas por 'locale' y 'group'
    $translations = L10n::all()->groupBy('locale')->map(function ($localeGroup) {
        return $localeGroup->groupBy('group');
    });

    // Verificar que las traducciones están agrupadas correctamente
    $this->assertArrayHasKey('es', $translations);
    $this->assertArrayHasKey('auth', $translations['es']);
    $this->assertArrayHasKey('language', $translations['es']);
});

