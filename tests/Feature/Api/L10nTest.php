<?php

use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class)->in(__DIR__);

// Test de API: Verificar que el endpoint devuelva los idiomas de traducción disponibles
it('returns the available translation locales', function () {
    // Crear algunas traducciones con diferentes idiomas
    L10n::factory()->create(['locale' => 'es', 'key' => 'app.title', 'value' => 'Mi Aplicación']);
    L10n::factory()->create(['locale' => 'en', 'key' => 'app.title', 'value' => 'My Application']);
    L10n::factory()->create(['locale' => 'de', 'key' => 'app.title', 'value' => 'Meine Anwendung']);

    // Hacer una solicitud GET al endpoint
    $response = $this->getJson('/api/l10n/locales');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se devuelven los idiomas únicos de las traducciones
    $responseData = $response->json('locales');
    $this->assertEqualsCanonicalizing(['es', 'en', 'de'], $responseData);

    // Verificar que no haya duplicados en la lista de idiomas
    $locales = $response->json('locales');
    $this->assertCount(3, $locales);
});


