<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an authenticated user to view referral purchase points', function () {
    // Crear usuario autenticado y un usuario referido
    $user = User::factory()->create();
    $referredUser = User::factory()->create(['referrer_pass' => $user->pass]);

    // Crear comercio
    $commerce = Commerce::factory()->create();

    // Crear una compra asociada al usuario referido
    $purchase = Purchase::factory()->for($referredUser)->for($commerce)->create();

    // Crear distribución de puntos para la compra del referido
    $purchase->distributePoints();
    
    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Llamar al endpoint
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los datos de puntos generados se devuelven correctamente
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase->id, $responseData[0]['purchase_id']);
});



it('does not show non-referral purchase points', function () {
    // Crear usuario sin referidos y una compra directa
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear una compra que no sea de referidos
    $purchase = Purchase::factory()->for($user)->for($commerce)->create();
    $purchase->pointsDistribution()->create([
        'user_id' => $user->id,
        'points' => 100,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener los puntos de referidos
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa pero no contenga datos de puntos
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(0, $responseData);
});


// Test: Verifica que el usuario autenticado pueda ver varios registros de puntos de referidos
it('shows multiple referral purchase points for authenticated user', function () {
    // Crear un usuario principal y dos referidos
    $referrer = User::factory()->create();
    $referredUser1 = User::factory()->create(['referrer_pass' => $referrer->pass]);
    $referredUser2 = User::factory()->create(['referrer_pass' => $referrer->pass]);

    // Crear un comercio y compras hechas por los referidos
    $commerce = Commerce::factory()->create();
    $purchase1 = Purchase::factory()->for($referredUser1)->for($commerce)->create(['amount' => 1000]);
    $purchase2 = Purchase::factory()->for($referredUser2)->for($commerce)->create(['amount' => 2000]);

    // Asociar puntos generados en `purchase_user_points` para el usuario principal
    \DB::table('purchase_user_points')->insert([
        ['purchase_id' => $purchase1->id, 'user_id' => $referrer->id, 'points' => 50],
        ['purchase_id' => $purchase2->id, 'user_id' => $referrer->id, 'points' => 100],
    ]);

    // Autenticar al usuario principal
    Sanctum::actingAs($referrer, ['*']);

    // Llamar al endpoint para obtener los puntos generados por compras de referidos
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa y contenga ambos registros
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);
    $this->assertEquals(50, $responseData[0]['points']);
    $this->assertEquals(100, $responseData[1]['points']);
});

it('excludes direct purchase points from the authenticated user and includes only referral points', function () {
    // Crear usuario autenticado y referidos en distintos niveles
    $user = User::factory()->create();
    $firstLevelReferral = User::factory()->create(['referrer_pass' => $user->pass]);
    $secondLevelReferral = User::factory()->create(['referrer_pass' => $firstLevelReferral->pass]);

    // Crear comercio
    $commerce = Commerce::factory()->create();

    // Crear compras: una directa para el usuario y otras para los referidos
    $userPurchase = Purchase::factory()->for($user)->for($commerce)->create();
    $purchaseFirstLevel = Purchase::factory()->for($firstLevelReferral)->for($commerce)->create();
    $purchaseSecondLevel = Purchase::factory()->for($secondLevelReferral)->for($commerce)->create();

    // Distribuir puntos para cada compra
    $userPurchase->distributePoints();
    $purchaseFirstLevel->distributePoints();
    $purchaseSecondLevel->distributePoints();

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Llamar al endpoint
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar los datos de la respuesta para ver el contenido completo
    $responseData = $response->json('data');

    // Asegurarse de que no se incluye la compra directa del usuario
    $purchaseIds = collect($responseData)->pluck('purchase_id');
    $this->assertFalse($purchaseIds->contains($userPurchase->id));

    // Verificar que la compra del segundo nivel está presente y la del primer nivel también
    $this->assertTrue($purchaseIds->contains($purchaseSecondLevel->id));
    $this->assertTrue($purchaseIds->contains($purchaseFirstLevel->id));
});

// Test: Verifica que no devuelve nada si no hay puntos de referidos
it('returns no data if there are no referral points generated', function () {
    $user = User::factory()->create();

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Llamar al endpoint
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa y esté vacía
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(0, $responseData);
});

it('only includes points from referrals up to the 7th level and excludes the user\'s own purchases', function () {
    // Crear el usuario autenticado
    $user = User::factory()->create();

    // Crear una cadena de referidos de más de 7 niveles
    $previousUser = $user;
    $referralUsers = [];
    for ($level = 1; $level <= 10; $level++) {
        $referredUser = User::factory()->create(['referrer_pass' => $previousUser->pass]);
        $referralUsers[$level] = $referredUser;
        $previousUser = $referredUser;
    }

    // Crear compras asociadas a los referidos hasta el nivel 10, cada uno con puntos distribuidos
    $commerce = Commerce::factory()->create();
    $purchases = [];
    foreach ($referralUsers as $level => $referralUser) {
        $purchase = Purchase::factory()->for($referralUser)->for($commerce)->create();
        $purchase->distributePoints(); // Genera puntos para cada compra
        $purchases[$level] = $purchase;
    }

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Llamar al endpoint para obtener los puntos de referidos
    $response = $this->getJson('/api/user/referral-points');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Obtener los datos de la respuesta
    $responseData = $response->json('data');

    // IDs esperados de purchases de referidos hasta el nivel 7
    $expectedPurchaseIds = collect($purchases)->slice(0, 8)->pluck('id');

    $returnedPurchaseIds = collect($responseData)->pluck('purchase_id')->unique();
    $this->assertEquals($expectedPurchaseIds->sort()->values()->all(), $returnedPurchaseIds->sort()->values()->all());
});

