<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear un usuario antes de cada prueba
    $this->user = User::factory()->create([
        'language' => 'en', // Idioma por defecto
    ]);
});


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

it('creates a new user through the API', function () {
    // Datos de creación de usuario
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => 'en', // Puedes establecerlo explícitamente si lo deseas
    ];

    // Llamada a la API para crear un nuevo usuario
    $response = $this->postJson('/api/register', $userData);

    // Verifica que la respuesta sea exitosa
    $response->assertStatus(201);

    // Verifica que el usuario se haya creado en la base de datos
    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
        'name' => 'Test User',
        'language' => 'en', // Verifica que el idioma se haya guardado correctamente
    ]);

    $response->assertJsonStructure(['user' => ['name', 'email', 'language']]);
    $this->assertEquals('Test User', $response['user']['name']);
    $this->assertEquals('testuser@example.com', $response['user']['email']);
    $this->assertEquals('en', $response['user']['language']);
});


it('returns the profile of the authenticated user', function () {
    // Create a user
    $user = User::factory()->create();

    Sanctum::actingAs(
        $user,
        ['*']
    );

    // The API call
    $response = $this->getJson('/api/users');

    // Assert the response
    $response->assertStatus(200);

    // Assert the correct user data was returned
    $response->assertJsonFragment(['id' => $user->id]);
    $response->assertJsonFragment(['name' => $user->name]);
    $response->assertJsonFragment(['email' => $user->email]);
    
    // Assert the language is defaulting to English if none set
    $response->assertJsonFragment(['language' => 'en']);
});

it('returns the user points and all referral counts as 0 for a new user', function () {
    // Create a user with a set number of points
    $user = User::factory()->create([
        'points' => 100, // Ejemplo: El usuario tiene 100 puntos
    ]);

    // Simula el usuario autenticado
    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user/data
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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
    $response = $this->getJson('/api/users/data');

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

// NUEVO TEST PARA VERIFICAR IDIOMA
it('returns the user language and updates it', function () {
    // Crear un usuario con idioma por defecto
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => null, // Aquí puedes dejarlo como null o simplemente omitirlo
    ];

    // Realiza la solicitud al endpoint de registro
    $response = $this->postJson('/api/register', $userData);

    // Asegúrate de que la respuesta es correcta
    $response->assertStatus(201);

    $user = User::findOrFail($response['user']['id']);

    Sanctum::actingAs($user, ['*']);

    // Llamada al endpoint /api/user
    $response = $this->getJson('/api/user');

    // Verifica que el idioma devuelto sea el por defecto (inglés)
    $this->assertEquals('en', $response['language']);

    // Actualizar idioma del usuario
    $updateData = ['language' => 'es'];

    $response = $this->putJson('/api/user', $updateData);

    // Verifica que la actualización fue exitosa
    $response->assertStatus(200);

    // Llama nuevamente al endpoint /api/user
    $response = $this->getJson('/api/user');

    // Verifica que el idioma haya sido actualizado correctamente
    $this->assertEquals('es', $response['language']);
});

it('retrieves the authenticated user', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Llamar al endpoint
    $response = $this->getJson('/api/user');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la respuesta contenga los datos del usuario
    $response->assertJson([
        'id' => $this->user->id,
        'name' => $this->user->name,
        'email' => $this->user->email,
        'language' => 'en',
    ]);
});

it('updates the authenticated user language', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Datos para actualizar
    $updateData = ['language' => 'es'];

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que la actualización fue exitosa
    $response->assertStatus(200);

    // Refrescar el objeto del usuario
    $this->user->refresh();

    // Verificar que el idioma se haya actualizado
    expect($this->user->language)->toBe('es');
});

it('returns validation errors when updating with invalid data', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Datos inválidos para actualizar
    $updateData = ['language' => '']; // Lenguaje vacío no es válido

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que se devuelvan errores de validación
    $response->assertStatus(422);
});

it('updates the authenticated user name', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Datos para actualizar el nombre
    $updateData = ['name' => 'Updated User'];

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que la actualización fue exitosa
    $response->assertStatus(200);

    // Refrescar el objeto del usuario
    $this->user->refresh();

    // Verificar que el nombre se haya actualizado
    expect($this->user->name)->toBe('Updated User');
});

it('returns validation errors when updating email with existing email', function () {
    // Crear otro usuario
    $anotherUser = User::factory()->create([
        'email' => 'otheruser@example.com',
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Intentar actualizar el email a uno existente
    $updateData = ['email' => 'otheruser@example.com'];

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que se devuelvan errores de validación
    $response->assertStatus(422);
});

it('returns validation errors when updating with invalid email', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Intentar actualizar el email a uno inválido
    $updateData = ['email' => 'invalid-email'];

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que se devuelvan errores de validación
    $response->assertStatus(422);
});

it('returns validation errors when updating with invalid password', function () {
    // Autenticar al usuario
    Sanctum::actingAs($this->user, ['*']);

    // Intentar actualizar con una contraseña corta
    $updateData = ['password' => 'short'];

    // Llamar al endpoint para actualizar
    $response = $this->putJson('/api/user', $updateData);

    // Verificar que se devuelvan errores de validación
    $response->assertStatus(422);
});

