<?php

use App\Models\{
	User,
	Commerce,
	Purchase,
	Entity
};
use App\Helpers\ConversionHelper;

use Illuminate\Foundation\Testing\RefreshDatabase;


it('can make a purchase', function () {
    // Crear User, Commerce y Compra usando Laravel Factories
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->make();
    
    // Asociar la compra al User y al Commerce
    $purchase->user()->associate($user);
    $purchase->commerce()->associate($commerce);
    $purchase->save();
    
    // Asserts
    expect($purchase->user_id)->toBe($user->id);
    expect($purchase->commerce_id)->toBe($commerce->id);
});

it('calculates points correctly', function () {
    // Crear User, Commerce y Compra usando Laravel Factories
    $user = User::factory()->create();
    $entity = Entity::factory()->make(['percent' => 10]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
	$commerce->entity()->save($entity);
	$purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(10)]);

	$commerce->load('entity');

    // Calcular puntos
    $points = $purchase->points;

    // Assert
    expect($points)->toBe(100.0); // Esperamos 100 puntos (10% de 1000 centavos)

    expect($purchase->gived_to_users_points)->toBe(0.0); // Como aún no se han distribuido los puntos
    expect($purchase->donated_points)->toBe(0.0); // Como aún no se han distribuido los puntos
});

it('distributes points correctly among referrers', function () {
    // Primero, creamos una serie de usuarios referidos
    $users = User::factory()->count(9)->create();

    for ($i = 0; $i < 8; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $entity = Entity::factory()->make(['percent' => 10]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
	$commerce->entity()->save($entity);
    // Luego, creamos una compra asociada al primer usuario
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);

	$commerce->load('entity');

    // Llamamos al método distributePoints()
    $purchase->distributePoints();

    expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toBe($purchase->fresh()->points);
	
	// Recargar los modelos de usuario
	$users = $users->map(function ($user) {
		return $user->fresh();
	});

	$pointsArr = $users->map(function ($user) {
		return $user->points;
	});

    for ($i = 0; $i < 8; $i++) {
		$expected_points = $purchase->points * (0.25) / pow(2, $i);
    }

	// Finalmente, verificamos que los puntos restantes se han asignado al comercio
	$remainingPoints = floatval($purchase->points); // Convierte a flotante primero
	for ($i = 0; $i < 8; $i++) {
		$remainingPoints -= (floatval($purchase->points) * (0.25 / pow(2, $i)));
	}

	expect($purchase->commerce->fresh()->gived_points)->toBe(200.0);
	expect($purchase->commerce->fresh()->donated_points)->toBe($remainingPoints);
});

it('distributes points correctly among incomplete referrers chain', function () {
    // Crear una cadena de referidos de 3 niveles
    $users = User::factory()->count(4)->create();

    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $entity = Entity::factory()->make(['percent' => 10]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
    $commerce->entity()->save($entity);
    // Crear una compra asociada al primer usuario
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);

    $commerce->load('entity');

    // Llamar al método distributePoints()
    $purchase->distributePoints();

	expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toBe($purchase->fresh()->points);

    // Recargar los modelos de usuario
    $users = $users->map(function ($user) {
        return $user->fresh();
    });

    $pointsArr = $users->map(function ($user) {
        return $user->points;
    });

    for ($i = 0; $i < 3; $i++) {
        $expected_points = $purchase->points * (0.25) / pow(2, $i);
        expect($users[$i]->points)->toBe($expected_points);
    }

    // Finalmente, verificar que los puntos restantes se han asignado al comercio
	$remainingPoints = floatval($purchase->points); // Convierte a flotante primero
	for ($i = 0; $i < count($users); $i++) {
        $remainingPoints -= (floatval($purchase->points) * (0.25 / pow(2, $i)));
    }

	expect($purchase->commerce->fresh()->gived_points)->toBe(200.0);
    expect($purchase->commerce->fresh()->donated_points)->toBe($remainingPoints);
    expect($purchase->commerce->fresh()->donated_points)->toBe(106.25);
});

it('can generate a QR code for payment', function () {
    $user = User::factory()->create(['points' => 500]);
    $commerce = Commerce::factory()->create();
    
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(2000)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    $commerce->createQrPayCode($purchase);

    // Verificar que el código QR contiene el enlace correcto
    // Suponemos que el método createQrCode retorna una cadena (string) con el contenido del código QR.
    $uuid = $purchase->uuid;
	$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

    // Verifica que el UUID está en la URL
    expect(str_contains($url, $uuid))->toBeTrue();
});

it('distributes points correctly after payment', function () {
	$referrer_test = User::factory()->create(['points' => 225]);
    $user = User::factory()->create(['points' => 500, 'referrer_pass' => $referrer_test->pass]);
    $entity = Entity::factory()->make(['percent' => 10]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
    $commerce->entity()->save($entity);
   
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(20)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    // Llama a la ruta de pago (asumiendo que es una solicitud POST)
    $response = $this->actingAs($user)
					 ->post(route('purchase.pay', ['uuid' => $purchase->uuid]));

    // Asegúrate de que el pago fue exitoso
    $response->assertStatus(200);

    // Recargamos el usuario desde la base de datos para obtener los puntos actualizados
    $user->refresh();

    // Verificar que los puntos del usuario han aumentado correctamente
    // Suponiendo que cada punto corresponde a 1 unidad de 'amount'
    expect($user->points)->toBe(550.0);

    // Aquí también puedes verificar si los puntos se distribuyeron correctamente entre los referidos
    // Deberías reemplazar 'referrer' con la relación real que utilizas para los referidos
    $referrer = $user->referrer;
    $referrer->refresh();
    expect($referrer->points)->toBe(250.0);
});

it('can pre-create a purchase', function () {
    $user = User::factory()->create(['points' => 500, 'pass' => 'DE-X88X88X']);
    $commerce = Commerce::factory()->create();

    $response = $this->post(route('preCreatePurchase'), [
        'amount' => ConversionHelper::moneyToPoints(100),
		'userPass' => $user->pass,
        'commerceId' => $commerce->id,
    ]);

    $response->assertStatus(200);

	// Verificar que se creó la compra
    $purchase = Purchase::where([
        'amount' => ConversionHelper::moneyToPoints(100),
        'commerce_id' => $commerce->id,
        'user_id' => null,
    ])->first();

    $this->assertNotNull($purchase);

    // Verificar que se devuelve la información correcta del usuario
    $response->assertJson([
        'user' => [
            'id' => $user->id,
            // Incluir cualquier otra información del usuario que estés devolviendo
        ],
        'purchase' => [
            'amount' => ConversionHelper::moneyToPoints(100),
            // Incluir cualquier otra información de la compra que estés devolviendo
        ],
    ]);

	// Asegurarse de que la respuesta incluye la URL de pago
    $responseContent = json_decode($response->getContent(), true);
    expect(isset($responseContent['url']))->toBeTrue();
    expect(str_contains($responseContent['url'], 'purchase/pay'))->toBeTrue();

	// Verificar que la URL incluye el UUID de la compra
    expect(str_contains($responseContent['url'], $purchase->uuid))->toBeTrue();
});

it('can pay a pre-created purchase', function () {
    $user = User::factory()->create(['points' => 500]);
	$entity = Entity::factory()->make(['percent' => 10]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
    $commerce->entity()->save($entity);

    $response = $this->post(route('preCreatePurchase'), [
        'amount' => ConversionHelper::moneyToPoints(100),
        'userPass' => $user->pass,
        'commerceId' => $commerce->id,
    ]);

    $response->assertStatus(200);

    // Verificar que se creó la compra
    $purchase = Purchase::where([
        'amount' => ConversionHelper::moneyToPoints(100),
        'commerce_id' => $commerce->id,
        'user_id' => null,
    ])->first();

    $this->assertNotNull($purchase);

    // Extraer la URL de la respuesta
    $responseContent = json_decode($response->getContent(), true);
    $paymentUrl = $responseContent['url'];

    // Hacer una solicitud GET a la URL de pago
    $paymentResponse = $this->actingAs($user)->get($paymentUrl);

    // Asegurarse de que la solicitud fue exitosa
    $paymentResponse->assertStatus(200);

    // Verificar que la compra fue pagada
    $purchase->refresh();
    $this->assertNotNull($purchase->paid_at);

	// Verificar si usuario esta siendo actualizado
    expect($user->fresh()->points)->toBe(750.0);
});

