<?php

use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    
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


it('retrieves translations for a given locale and group', function () {
    
    $translations = L10n::where('locale', 'es')->where('group', 'auth')->get();

    
    $this->assertEquals('Iniciar sesión', $translations->firstWhere('key', 'login')->value);
    $this->assertEquals('Cerrar sesión', $translations->firstWhere('key', 'logout')->value);
});


it('creates a new translation in a group', function () {
    
    $newTranslation = L10n::create([
        'locale' => 'es',
        'group' => 'auth',
        'key' => 'register',
        'value' => 'Registrar',
    ]);

    
    $this->assertDatabaseHas('l10ns', [
        'key' => 'register',
        'value' => 'Registrar',
    ]);
});


it('updates an existing translation', function () {
    
    $translation = L10n::where('key', 'logout')->where('group', 'auth')->first();
    $translation->update(['value' => 'Salir']);

    
    $this->assertDatabaseHas('l10ns', [
        'key' => 'logout',
        'value' => 'Salir',
    ]);
});


it('deletes a translation', function () {
    
    $translation = L10n::where('key', 'login')->where('group', 'auth')->first();
    $translation->delete();

    
    $this->assertDatabaseMissing('l10ns', [
        'key' => 'login',
    ]);
});



it('has the correct attributes', function () {
    
    $translation = L10n::create([
        'locale' => 'en',
        'group' => 'auth',
        'key' => 'greeting',
        'value' => 'Hello',
    ]);

    
    $this->assertEquals('en', $translation->locale);
    $this->assertEquals('auth', $translation->group);
    $this->assertEquals('greeting', $translation->key);
    $this->assertEquals('Hello', $translation->value);
});


it('retrieves all translations by group', function () {
    
    $translations = L10n::where('group', 'auth')->get();

    
    $this->assertEquals(2, $translations->count()); 
});


it('retrieves a single translation', function () {
    
    $translation = L10n::where('key', 'login')->where('group', 'auth')->first();

    
    $this->assertEquals('Iniciar sesión', $translation->value);
});


it('retrieves translations grouped by locale and group', function () {
    
    $translations = L10n::all()->groupBy('locale')->map(function ($localeGroup) {
        return $localeGroup->groupBy('group');
    });

    
    $this->assertArrayHasKey('es', $translations);
    $this->assertArrayHasKey('auth', $translations['es']);
    $this->assertArrayHasKey('language', $translations['es']);
});

