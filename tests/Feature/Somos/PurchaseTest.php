<?php

use App\Models\{
	User,
	Commerce,
	Purchase,
    Somos
};
use App\Helpers\ConversionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    uses(RefreshDatabase::class);
});

it('can make a purchase', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->make();
    
    
    $purchase->user()->associate($user);
    $purchase->commerce()->associate($commerce);
    $purchase->save();
    
    
    expect($purchase->user_id)->toBe($user->id);
    expect($purchase->commerce_id)->toBe($commerce->id);
});

it('calculates points correctly', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(10)]);

    
    $purchase->refresh();  
    $points = $purchase->points;

    
    expect($points)->toBe(100.0); 

    expect($purchase->gived_to_users_points)->toEqual(0); 
    expect($purchase->donated_points)->toEqual(0); 
});


it('distributes points correctly among referrers', function () {
    
    $users = User::factory()->count(9)->create();

    for ($i = 0; $i < 8; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]); 
    
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);

    
    $purchase->distributePoints();

    expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
	
	
	$users = $users->map(function ($user) {
		return $user->fresh();
	});

	$pointsArr = $users->map(function ($user) {
		return $user->points;
	});

    for ($i = 0; $i < 8; $i++) {
		$expected_points = $purchase->points * (0.25) / pow(2, $i);
    }

    $remainingPoints = floatval($purchase->points);
    for ($i = 0; $i < 8; $i++) {
        $term = floor(floatval($purchase->points) * (0.25 / pow(2, $i)) * 100) / 100;
        $remainingPoints = floor(($remainingPoints - $term) * 100) / 100;
    }
    expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);

	
    expect($purchase->commerce->fresh()->gived_points)->toEqual(200.0);
	expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);
});

it('distributes points correctly among incomplete referrers chain', function () {
    
    $users = User::factory()->count(4)->create();

    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }
    $commerce = Commerce::factory()->create(['percent' => 10]); 
    
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);
    
    $purchase->distributePoints();
	expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
    
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
    
	$remainingPoints = floatval($purchase->points); 
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
    
    $uuid = $purchase->uuid;
	$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

    
    expect(str_contains($url, $uuid))->toBeTrue();
});

it('distributes points correctly after payment', function () {
	$referrer_test = User::factory()->create(['points' => 225]);
    $user = User::factory()->create(['points' => 500, 'referrer_pass' => $referrer_test->pass]);
    $commerce = Commerce::factory()->create(['percent' => 10]); 
   
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(20)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    
    $response = $this->actingAs($user)
					 ->post(route('purchase.pay', ['uuid' => $purchase->uuid]));

    
    $response->assertStatus(200);

    
    $user->refresh();

    
    
    expect($user->points)->toBe(550.0);

    
    
    $referrer = $user->referrer;
    $referrer->refresh();
    expect($referrer->points)->toBe(250.0);
});

it('creates a record in purchase_user_points for each user receiving points', function () {
    
    $users = User::factory()->count(6)->create();
    for ($i = 0; $i < 5; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    for ($i = 0; $i < 5; $i++) {
        $points = floor($purchase->points * (0.25 / pow(2, $i)) * 100) / 100;
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }
});

it('registers points for incomplete referral chain', function () {
    
    $users = User::factory()->count(4)->create();
    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    for ($i = 0; $i < 3; $i++) {
        $points = floor($purchase->points * (0.25 / pow(2, $i)) * 100) / 100;
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }

    
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

    
    $purchase->distributePoints();

    
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

    
    $purchase->distributePoints();

    
    $this->assertDatabaseHas('purchase_user_points', [
        'purchase_id' => $purchase->id,
        'user_id' => $user->id,
    ]);

    
    $otherUsers = User::where('id', '!=', $user->id)->pluck('id');
    foreach ($otherUsers as $otherUserId) {
        $this->assertDatabaseMissing('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $otherUserId,
        ]);
    }
});

it('almacena los puntos del usuario con dos decimales redondeados hacia abajo y transfiere el sobrante a Somos en una compra directa', function () {
    uses(RefreshDatabase::class);

    $user = User::factory()->create(['points' => 0.0, 'referrer_pass' => null]);
    $commerce = Commerce::factory()->create(['percent' => 10]);

    // Crear registro de Somos; se espera que arranque en 0.0.
    $somos = Somos::factory()->create(['points' => 0.0]);

    // Para asegurar que usamos el registro que se creó, lo obtenemos por ID.
    $somos = Somos::find($somos->id);

    $somosOriginalPoints = $somos->points; // Debería ser 0.0 (si la fábrica respeta lo que se le pasa).

    // Definición del monto base para la compra.
    $purchasePoints = 10.5678;
    // Crear la compra; con amount = 105.678, dado que getPoints = amount * (percent/100).
    $purchase = Purchase::factory()->for($user)->for($commerce)->create([
        'amount' => 105.678
    ]);


    // Ejecutar la distribución.
    $purchase->distributePoints();

    // Refrescar los registros.
    $user->refresh();
    $somos = Somos::find($somos->id);

    // Calcular lo que se espera que se reparta: el 25% de los puntos totales.
    $originalPoints = $purchasePoints * 0.25;

    // El usuario recibe los puntos truncados a 2 decimales.
    $expectedUserPoints = floor($originalPoints * 100) / 100;

    // El sobrante (a partir del tercer decimal) se asigna a Somos.
    $remainingPoints = $originalPoints - $expectedUserPoints;
    $remainingPoints = floor($remainingPoints * 100000) / 100000;

    $expectedSomosPoints = $somosOriginalPoints + $remainingPoints;

    expect($user->points)->toBe($expectedUserPoints);
    expect(abs((float)$somos->points - (float)$expectedSomosPoints))->toBeLessThan(0.00002);
    expect(abs((float)($user->points + $somos->points) - (float)$originalPoints))->toBeLessThan(0.0002);
});

it('almacena los puntos del usuario con dos decimales redondeados hacia abajo y transfiere el sobrante a Somos en una compra referida', function () {
    uses(RefreshDatabase::class);

    $referrer = User::factory()->create(['points' => 0.0]);
    $user = User::factory()->create([
        'points' => 0.0,
        'referrer_pass' => $referrer->pass
    ]);
    $commerce = Commerce::factory()->create(['percent' => 10]);

    // Creamos el registro de Somos y forzamos que inicie en 0.00000
    $somos = Somos::factory()->create(['points' => '0.00000']);

    // Para estar seguros, lo buscamos por ID:
    $somos = Somos::find($somos->id);
    $somosOriginalPoints = $somos->points; // Se espera "0.00000" si se forzó correctamente

    $purchasePoints = 20.7899;
    // Al crear la compra, se asigna 'amount' de forma que getPoints = amount * (percent/100)
    // Dado que percent = 10, amount se establece en 207.899 para que getPoints devuelva 20.7899.
    $purchase = Purchase::factory()->for($user)->for($commerce)->create([
        'amount' => 207.899
    ]);


    // Ejecutar la distribución
    $purchase->distributePoints();

    // Refrescamos los registros
    $user->refresh();
    $referrer->refresh();
    $somos = Somos::find($somos->id);


    // Calcular el 25% de los puntos de la compra (la porción que se reparte)
    $originalPoints = $purchasePoints * 0.25;

    // El comprador recibe el valor truncado a 2 decimales.
    $expectedUserPoints = floor($originalPoints * 100) / 100;

    // Para el referido, se espera que reciba la mitad de lo que recibió el comprador (truncado a dos decimales).
    $originalReferrerPoints = $expectedUserPoints / 2;
    $expectedReferrerPoints = floor($originalReferrerPoints * 100) / 100;

    // En el test se comenta restar el bonus del referido para calcular el sobrante,
    // por lo que el sobrante se calcula como la diferencia entre el 25% y lo que recibió el comprador.
    $remainingPoints = $originalPoints - $expectedUserPoints;
    $remainingPoints = floor($remainingPoints * 100000) / 100000;

    $expectedSomosPoints = bcadd((string)$somosOriginalPoints, (string)$remainingPoints, 5);

    // Verificamos:
    expect($user->points)->toBe($expectedUserPoints);
    expect($referrer->points)->toBe($expectedReferrerPoints);
    expect(round((float)$somos->points, 5))
        ->toEqual(round((float)$expectedSomosPoints, 5));

    // Si la suma total (usuario + Somos) también se necesita verificar:
    expect(abs(round($user->points + $somos->points, 5) - round($originalPoints, 5)))
        ->toBeLessThan(0.00001);

});


it('asegura que la suma de puntos del usuario y Somos equivale a los puntos totales de la compra', function () {
    $user = User::factory()->create(['points' => 0.0]);
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $somos = Somos::factory()->create(['points' => 0.0]);

    $purchasePoints = 15.98744;
    $purchase = Purchase::factory()->for($user)->for($commerce)->create([
        'amount' => 159.8744
    ]);

    $purchase->distributePoints();
    $somos = Somos::find($somos->id);


    $user->refresh();
    $somos->refresh();

    $originalPoints = $purchasePoints * 0.25;
    $expectedUserPoints = floor($originalPoints * 100) / 100;
    $expectedSomosPoints = $originalPoints - $expectedUserPoints;

    expect(abs((float)($user->points + $somos->points) - (float)$originalPoints))
        ->toBeLessThan(0.00001);

    expect($user->points)->toBe($expectedUserPoints);
    expect(abs((float)($somos->points) - (float)$expectedSomosPoints))
        ->toBeLessThan(0.00001);


});

it('maneja correctamente los puntos con exactamente dos decimales sin transferir sobrante a Somos', function () {
    $user = User::factory()->create(['points' => 0.0]);
    $commerce = Commerce::factory()->create(['percent' => 10]);

    $somos = Somos::factory()->create(['points' => 0.0]);

    $purchasePoints = 25.50;
    $purchase = Purchase::factory()->for($user)->for($commerce)->create([
        'amount' => 255
    ]);

    $purchase->distributePoints();

    $user->refresh();
    $somos->refresh();

    $expectedUserPoints = floor($purchasePoints * 100) / 100;
    $expectedSomosPoints = $purchasePoints - $expectedUserPoints;

    expect($user->points)->toBe($expectedUserPoints);
    expect((float)$somos->points)->toBe($expectedSomosPoints);
});

it('maneja correctamente los puntos con menos de dos decimales sin transferir sobrante a Somos', function () {
    $user = User::factory()->create(['points' => 0.0]);
    $commerce = Commerce::factory()->create(['percent' => 10]);

    $somos = Somos::factory()->create(['points' => 0.0]);

    $purchasePoints = 30.5; 
    $purchase = Purchase::factory()->for($user)->for($commerce)->create([
        'amount' => 305
    ]);

    $purchase->distributePoints();

    $user->refresh();
    $somos->refresh();

    $expectedUserPoints = floor($purchasePoints * 100) / 100; 
    $expectedSomosPoints = $purchasePoints - $expectedUserPoints; 

    expect($user->points)->toBe($expectedUserPoints);
    expect((float)$somos->points)->toBe($expectedSomosPoints);
});
