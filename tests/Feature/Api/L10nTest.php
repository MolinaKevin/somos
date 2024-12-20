<?php

use App\Models\L10n;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class)->in(__DIR__);


it('returns the available translation locales', function () {
    
    L10n::factory()->create(['locale' => 'es', 'key' => 'app.title', 'value' => 'Mi Aplicación']);
    L10n::factory()->create(['locale' => 'en', 'key' => 'app.title', 'value' => 'My Application']);
    L10n::factory()->create(['locale' => 'de', 'key' => 'app.title', 'value' => 'Meine Anwendung']);

    
    $response = $this->getJson('/api/l10n/locales');

    
    $response->assertStatus(200);

    
    $responseData = $response->json('locales');
    $this->assertEqualsCanonicalizing(['es', 'en', 'de'], $responseData);

    
    $locales = $response->json('locales');
    $this->assertCount(3, $locales);
});


