<?php

use App\Models\{
	User,
	Commerce,
	Purchase,
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
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(10)]);

    // Calcular puntos
    $purchase->refresh();  // Recargar el modelo para asegurarse de que los puntos se calculen correctamente
    $points = $purchase->points;

    // Assert
    expect($points)->toBe(100.0); // Esperamos 100 puntos (10% de 1000 centavos)

    expect($purchase->gived_to_users_points)->toEqual(0); // Como aún no se han distribuido los puntos
    expect($purchase->donated_points)->toEqual(0); // Como aún no se han distribuido los puntos
});


it('distributes points correctly among referrers', function () {
    // Primero, creamos una serie de usuarios referidos
    $users = User::factory()->count(9)->create();

    for ($i = 0; $i < 8; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]); 
    // Luego, creamos una compra asociada al primer usuario
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);

    // Llamamos al método distributePoints()
    $purchase->distributePoints();

    expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
	
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

	expect($purchase->commerce->fresh()->gived_points)->toEqual(200.0);
	expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);
});

it('distributes points correctly among incomplete referrers chain', function () {
    // Crear una cadena de referidos de 3 niveles
    $users = User::factory()->count(4)->create();

    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }
    $commerce = Commerce::factory()->create(['percent' => 10]); 
    // Crear una compra asociada al primer usuario
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);
    // Llamar al método distributePoints()
    $purchase->distributePoints();
	expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
    // Recargar los modelos de usuario
    $users = $users->map(function ($user) {
        return $user->fresh();
    });

    $pointsArr = $users->map(function ($user) {
        return $user->points;
    });
    for ($i = 0; $i < 3; $i++) {
        $expected_points = $purchase->points * (0.25) / pow(2, $i);
        expect($users[$i]->points)->toEqual($expected_points);
    }
    // Finalmente, verificar que los puntos restantes se han asignado al comercio
	$remainingPoints = floatval($purchase->points); // Convierte a flotante primero
	for ($i = 0; $i < count($users); $i++) {
        $remainingPoints -= (floatval($purchase->points) * (0.25 / pow(2, $i)));
    }

	expect($purchase->commerce->fresh()->gived_points)->toEqual(200.0);
    expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);
    expect($purchase->commerce->fresh()->donated_points)->toEqual(106.25);
});

it('can generate a QR code for payment', function () {
    $user = User::factory()->create(['points' => 500]);
    $commerce = Commerce::factory()->create();
    
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(2000)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    //$commerce->createQrPayCode($purchase);

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
    $commerce = Commerce::factory()->create(['percent' => 10]); 
   
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
    // Suponiendo que cada punto corresponde a 1 unidad de 'points'
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
    $commerce = Commerce::factory()->create(['percent' => 10]); 

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

it('creates a record in purchase_user_points for each user receiving points', function () {
    // Crear una cadena de usuarios referidos de 5 niveles
    $users = User::factory()->count(6)->create();
    for ($i = 0; $i < 5; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    // Llamar al método de distribución de puntos
    $purchase->distributePoints();

    // Verificar que cada usuario en la cadena ha recibido puntos y que el registro existe en `purchase_user_points`
    for ($i = 0; $i < 5; $i++) {
        $points = round($purchase->points * (0.25 / pow(2, $i)), 2); // Redondea a 2 decimales
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }
});

it('registers points for incomplete referral chain', function () {
    // Crear una cadena de referidos de 3 niveles
    $users = User::factory()->count(4)->create();
    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    // Distribuir puntos
    $purchase->distributePoints();

    // Verificar que solo se registraron puntos hasta el tercer nivel
    for ($i = 0; $i < 3; $i++) {
        $points = round($purchase->points * (0.25 / pow(2, $i)), 2); // Redondea para evitar problemas de precisión
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }

    // Verificar que no hay registros de puntos para el cuarto usuario (sin referrer)
    $this->assertDatabaseHas('purchase_user_points', [
        'purchase_id' => $purchase->id,
        'user_id' => $users[3]->id,
    ]);
});

it('verifies correct point totals for each user in purchase_user_points', function () {
    $users = User::factory()->count(6)->create();
    for ($i = 0; $i < 5; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    // Distribuir puntos
    $purchase->distributePoints();

    // Verificar el total de puntos recibidos por cada usuario en `purchase_user_points`
    for ($i = 0; $i < 5; $i++) {
        $expectedPoints = $purchase->points * (0.25 / pow(2, $i));
        $totalPoints = \DB::table('purchase_user_points')
            ->where('user_id', $users[$i]->id)
            ->sum('points');

        expect(abs($totalPoints - $expectedPoints))->toBeLessThan(0.01);

    }
});

it('ensures no points are registered for non-referral purchases', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    // Distribuir puntos
    $purchase->distributePoints();

    // Verificar que solo el usuario inicial recibe puntos y no hay otros registros en `purchase_user_points`
    $this->assertDatabaseHas('purchase_user_points', [
        'purchase_id' => $purchase->id,
        'user_id' => $user->id,
    ]);

    // Verificar que no hay otros registros en `purchase_user_points` para otros usuarios
    $otherUsers = User::where('id', '!=', $user->id)->pluck('id');
    foreach ($otherUsers as $otherUserId) {
        $this->assertDatabaseMissing('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $otherUserId,
        ]);
    }
});

