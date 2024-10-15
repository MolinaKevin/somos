<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('logs in an existing user through the API', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
    ]);

    // Login data
    $loginData = [
        'email' => 'testuser@example.com',
        'password' => 'password',
    ];

    // The API call
    $response = $this->postJson('/api/login', $loginData);

    // Assert the response
    $response->assertStatus(200);

    // Assert the login was successful (you might return user data or a token)
    $this->assertNotNull($response['user']);
    $this->assertNotNull($response['token']);
});

it('returns the profile of the authenticated user', function () {
    // Create a user
    $user = User::factory()->create();

    Sanctum::actingAs(
        $user,
        ['*']
    );

    // The API call
    $response = $this->getJson('/api/user');

    // Assert the response
    $response->assertStatus(200);

    // Assert the correct user data was returned
    $this->assertEquals($user->id, $response['id']);
    $this->assertEquals($user->name, $response['name']);
    $this->assertEquals($user->email, $response['email']);
});


it('returns the user points and all referral counts as 0 for a new user', function () {
    // Create a user with a set number of points
    $user = User::factory()->create([
        'points' => 100, // Ejemplo: El usuario tiene 100 puntos
    ]);

    // Simula el usuario autenticado
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los puntos del usuario se devuelven correctamente
    $this->assertEquals($user->points, $response['points']);

    // Verifica que todos los referidos en los niveles 1 al 7 son 0
    for ($level = 1; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});


it('returns the user points and only level 1 referrals count', function () {
    // Create a user with a set number of points
    $user = User::factory()->create([
        'points' => 100, // Ejemplo: El usuario tiene 100 puntos
    ]);

    // Crea 3 referidos en el nivel 1
    $referralsLevel1 = User::factory()->count(3)->create([
        'referrer_pass' => $user->pass,
    ]);

    // Simula el usuario autenticado
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los puntos del usuario se devuelven correctamente
    $this->assertEquals($user->points, $response['points']);

    // Verifica que solo el nivel 1 tiene referidos (3 en este caso)
    $this->assertEquals(3, $response['referrals']['level_1']);

    // Verifica que los niveles del 2 al 7 están en 0
    for ($level = 2; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and level 1 and 2 referrals count', function () {
    // Crear el usuario principal
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS1', // El código de referencia del usuario principal
    ]);

    // Crear 3 usuarios referidos en el nivel 1
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS1', // Todos estos usuarios son referidos por el usuario principal
    ]);

    // Crear 2 usuarios referidos en el nivel 2 (referidos por uno de los usuarios de nivel 1)
    User::factory()->count(2)->create([
        'referrer_pass' => $level1Referrals->first()->pass, // Referidos por el primer usuario de nivel 1
    ]);

    // Autenticar al usuario principal
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los puntos del usuario se devuelven correctamente
    $this->assertEquals($user->points, $response['points']);

    // Verifica que el nivel 1 tiene 3 referidos
    $this->assertEquals(3, $response['referrals']['level_1']);

    // Verifica que el nivel 2 tiene 2 referidos
    $this->assertEquals(2, $response['referrals']['level_2']);

    // Verifica que los niveles del 3 al 7 están en 0
    for ($level = 3; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and level 1 and 2 referrals count with random level 2 referrals', function () {
    // Crear el usuario principal
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS1', // El código de referencia del usuario principal
    ]);

    // Crear 3 usuarios referidos en el nivel 1
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS1', // Todos estos usuarios son referidos por el usuario principal
    ]);

    // Crear 2 usuarios referidos en el nivel 2, referidos por dos usuarios diferentes de nivel 1
    User::factory()->create([
        'referrer_pass' => $level1Referrals[0]->pass, // Referido por el primer usuario de nivel 1
    ]);

    User::factory()->create([
        'referrer_pass' => $level1Referrals[1]->pass, // Referido por el segundo usuario de nivel 1
    ]);

    // Autenticar al usuario principal
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los puntos del usuario se devuelven correctamente
    $this->assertEquals($user->points, $response['points']);

    // Verifica que el nivel 1 tiene 3 referidos
    $this->assertEquals(3, $response['referrals']['level_1']);

    // Verifica que el nivel 2 tiene 2 referidos
    $this->assertEquals(2, $response['referrals']['level_2']);

    // Verifica que los niveles del 3 al 7 están en 0
    for ($level = 3; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and referral counts for levels 0 to 7', function () {
    // Crear el usuario principal (nivel 0)
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS0', // Código de referencia del usuario principal (nivel 0)
    ]);

    // Crear referidos en el nivel 1
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS0', // Referidos por el usuario principal
    ]);

    // Crear referidos en el nivel 2
    $level2Referrals = User::factory()->count(2)->create([
        'referrer_pass' => $level1Referrals[0]->pass, // Referidos por el primer usuario de nivel 1
    ]);

    // Crear referidos en el nivel 3
    $level3Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level2Referrals[0]->pass, // Referidos por el primer usuario de nivel 2
    ]);

    // Crear referidos en el nivel 4
    $level4Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level3Referrals[0]->pass, // Referidos por el primer usuario de nivel 3
    ]);

    // Crear referidos en el nivel 5
    $level5Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level4Referrals[0]->pass, // Referidos por el primer usuario de nivel 4
    ]);

    // Crear referidos en el nivel 6
    $level6Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level5Referrals[0]->pass, // Referidos por el primer usuario de nivel 5
    ]);

    // Crear referidos en el nivel 7
    $level7Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level6Referrals[0]->pass, // Referidos por el primer usuario de nivel 6
    ]);

    // Autenticar al usuario principal
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los puntos del usuario se devuelven correctamente
    $this->assertEquals($user->points, $response['points']);

    // Verifica los referidos por nivel
    $this->assertEquals(3, $response['referrals']['level_1']); // 3 en el nivel 1
    $this->assertEquals(2, $response['referrals']['level_2']); // 2 en el nivel 2
    $this->assertEquals(1, $response['referrals']['level_3']); // 1 en el nivel 3
    $this->assertEquals(1, $response['referrals']['level_4']); // 1 en el nivel 4
    $this->assertEquals(1, $response['referrals']['level_5']); // 1 en el nivel 5
    $this->assertEquals(1, $response['referrals']['level_6']); // 1 en el nivel 6
    $this->assertEquals(1, $response['referrals']['level_7']); // 1 en el nivel 7
});

it('returns only levels 1 to 7 referrals and ignores level 8 and above', function () {
    // Crear el usuario base
    $user = User::factory()->create([
        'points' => 100,
    ]);

    // Crear referidos de nivel 1
    $level1Referrals = User::factory()->count(3)->create(['referrer_pass' => $user->pass]);

    // Crear referidos de nivel 2
    $level2Referrals = collect();
    foreach ($level1Referrals as $level1User) {
        $level2Referrals = $level2Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level1User->pass])
        );
    }

    // Crear referidos de nivel 3
    $level3Referrals = collect();
    foreach ($level2Referrals as $level2User) {
        $level3Referrals = $level3Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level2User->pass])
        );
    }

    // Crear referidos de nivel 4
    $level4Referrals = collect();
    foreach ($level3Referrals as $level3User) {
        $level4Referrals = $level4Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level3User->pass])
        );
    }

    // Crear referidos de nivel 5
    $level5Referrals = collect();
    foreach ($level4Referrals as $level4User) {
        $level5Referrals = $level5Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level4User->pass])
        );
    }

    // Crear referidos de nivel 6
    $level6Referrals = collect();
    foreach ($level5Referrals as $level5User) {
        $level6Referrals = $level6Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level5User->pass])
        );
    }

    // Crear referidos de nivel 7
    $level7Referrals = collect();
    foreach ($level6Referrals as $level6User) {
        $level7Referrals = $level7Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level6User->pass])
        );
    }

    // Crear referidos de nivel 8 (que no deberían aparecer en la respuesta)
    $level8Referrals = collect();
    foreach ($level7Referrals as $level7User) {
        $level8Referrals = $level8Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level7User->pass])
        );
    }

    // Simula el usuario autenticado
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los niveles del 1 al 7 tienen referidos y están correctamente contados
    for ($level = 1; $level <= 7; $level++) {
        $this->assertTrue(isset($response['referrals']['level_' . $level]));
    }

    // Verifica que el nivel 8 no existe en la respuesta
    $this->assertFalse(array_key_exists('level_8', $response['referrals']));

});

it('returns levels 1 to 7 referrals and calculates lowlevelrefs as the sum of levels 2 to 7', function () {
    // Crear el usuario inicial (nivel 0)
    $user = User::factory()->create([
        'points' => 100,
    ]);

    // Crear usuarios de nivel 1 (hijos directos del usuario principal)
    $level1Users = User::factory()->count(3)->create(['referrer_pass' => $user->pass]);

    // Crear usuarios de nivel 2
    $level2Users = collect();
    foreach ($level1Users as $level1User) {
        $level2Users = $level2Users->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level1User->pass])
        );
    }

    // Crear usuarios de nivel 3 a 7
    $currentLevelUsers = $level2Users;
    $totalLowLevelRefs = $level2Users->count(); // Inicialmente, el total de referidos en lowlevelrefs

    for ($level = 3; $level <= 7; $level++) {
        $currentLevelUsers = $currentLevelUsers->flatMap(function ($user) use ($level) {
            return User::factory()->count(2)->create(['referrer_pass' => $user->pass]);
        });
        $totalLowLevelRefs += $currentLevelUsers->count(); // Sumar al total de lowlevelrefs
    }

    // Actuar como el usuario principal
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/user/data');

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verifica que los niveles del 1 al 7 están correctamente contados
    for ($level = 1; $level <= 7; $level++) {
        $this->assertTrue(isset($response['referrals']['level_' . $level]));
    }

    // Verifica que los referidos de los niveles 2 al 7 se suman correctamente en lowlevelrefs
    $this->assertEquals($totalLowLevelRefs, $response['lowlevelrefs']);

    // Verifica que el nivel 8 no está presente en la respuesta
    $this->assertFalse(array_key_exists('level_8', $response['referrals']));

    // Opcional: Verificar los valores individuales de los niveles
    $this->assertEquals(3, $response['referrals']['level_1']); // Nivel 1 debería tener 3 usuarios
    $this->assertEquals(6, $response['referrals']['level_2']); // Nivel 2 debería tener 6 usuarios (2 por cada usuario de nivel 1)
    // Y así sucesivamente para los demás niveles si deseas verificar
});

