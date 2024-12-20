<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    
    $this->user = User::factory()->create([
        'language' => 'en', 
    ]);
});


it('logs in an existing user through the API', function () {
    
    $user = User::factory()->create([
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
    ]);

    
    $loginData = [
        'email' => 'testuser@example.com',
        'password' => 'password',
    ];

    
    $response = $this->postJson('/api/login', $loginData);

    
    $response->assertStatus(200);

    
    $this->assertNotNull($response['user']);
    $this->assertNotNull($response['token']);
});

it('creates a new user through the API', function () {
    
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => 'en', 
    ];

    
    $response = $this->postJson('/api/register', $userData);

    
    $response->assertStatus(201);

    
    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
        'name' => 'Test User',
        'language' => 'en', 
    ]);

    $response->assertJsonStructure(['user' => ['name', 'email', 'language']]);
    $this->assertEquals('Test User', $response['user']['name']);
    $this->assertEquals('testuser@example.com', $response['user']['email']);
    $this->assertEquals('en', $response['user']['language']);
});


it('returns the profile of the authenticated user', function () {
    
    $user = User::factory()->create();

    Sanctum::actingAs(
        $user,
        ['*']
    );

    
    $response = $this->getJson('/api/user');

    
    $response->assertStatus(200);

    
    $response->assertJsonFragment(['id' => $user->id]);
    $response->assertJsonFragment(['name' => $user->name]);
    $response->assertJsonFragment(['email' => $user->email]);
    
    
    $response->assertJsonFragment(['language' => 'en']);
});

it('returns the user points and all referral counts as 0 for a new user', function () {
    
    $user = User::factory()->create([
        'points' => 100, 
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    $this->assertEquals($user->points, $response['points']);

    
    for ($level = 1; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and only level 1 referrals count', function () {
    
    $user = User::factory()->create([
        'points' => 100, 
    ]);

    
    $referralsLevel1 = User::factory()->count(3)->create([
        'referrer_pass' => $user->pass,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    $this->assertEquals($user->points, $response['points']);

    
    $this->assertEquals(3, $response['referrals']['level_1']);

    
    for ($level = 2; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and level 1 and 2 referrals count', function () {
    
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS1', 
    ]);

    
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS1', 
    ]);

    
    User::factory()->count(2)->create([
        'referrer_pass' => $level1Referrals->first()->pass, 
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    $this->assertEquals($user->points, $response['points']);

    
    $this->assertEquals(3, $response['referrals']['level_1']);

    
    $this->assertEquals(2, $response['referrals']['level_2']);

    
    for ($level = 3; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and level 1 and 2 referrals count with random level 2 referrals', function () {
    
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS1', 
    ]);

    
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS1', 
    ]);

    
    User::factory()->create([
        'referrer_pass' => $level1Referrals[0]->pass, 
    ]);

    User::factory()->create([
        'referrer_pass' => $level1Referrals[1]->pass, 
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    $this->assertEquals($user->points, $response['points']);

    
    $this->assertEquals(3, $response['referrals']['level_1']);

    
    $this->assertEquals(2, $response['referrals']['level_2']);

    
    for ($level = 3; $level <= 7; $level++) {
        $this->assertEquals(0, $response['referrals']['level_' . $level]);
    }
});

it('returns the user points and referral counts for levels 0 to 7', function () {
    
    $user = User::factory()->create([
        'points' => 100,
        'pass' => 'USERPASS0', 
    ]);

    
    $level1Referrals = User::factory()->count(3)->create([
        'referrer_pass' => 'USERPASS0', 
    ]);

    
    $level2Referrals = User::factory()->count(2)->create([
        'referrer_pass' => $level1Referrals[0]->pass, 
    ]);

    
    $level3Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level2Referrals[0]->pass, 
    ]);

    
    $level4Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level3Referrals[0]->pass, 
    ]);

    
    $level5Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level4Referrals[0]->pass, 
    ]);

    
    $level6Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level5Referrals[0]->pass, 
    ]);

    
    $level7Referrals = User::factory()->count(1)->create([
        'referrer_pass' => $level6Referrals[0]->pass, 
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    $this->assertEquals($user->points, $response['points']);

    
    $this->assertEquals(3, $response['referrals']['level_1']); 
    $this->assertEquals(2, $response['referrals']['level_2']); 
    $this->assertEquals(1, $response['referrals']['level_3']); 
    $this->assertEquals(1, $response['referrals']['level_4']); 
    $this->assertEquals(1, $response['referrals']['level_5']); 
    $this->assertEquals(1, $response['referrals']['level_6']); 
    $this->assertEquals(1, $response['referrals']['level_7']); 
});

it('returns only levels 1 to 7 referrals and ignores level 8 and above', function () {
    
    $user = User::factory()->create([
        'points' => 100,
    ]);

    
    $level1Referrals = User::factory()->count(3)->create(['referrer_pass' => $user->pass]);

    
    $level2Referrals = collect();
    foreach ($level1Referrals as $level1User) {
        $level2Referrals = $level2Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level1User->pass])
        );
    }

    
    $level3Referrals = collect();
    foreach ($level2Referrals as $level2User) {
        $level3Referrals = $level3Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level2User->pass])
        );
    }

    
    $level4Referrals = collect();
    foreach ($level3Referrals as $level3User) {
        $level4Referrals = $level4Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level3User->pass])
        );
    }

    
    $level5Referrals = collect();
    foreach ($level4Referrals as $level4User) {
        $level5Referrals = $level5Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level4User->pass])
        );
    }

    
    $level6Referrals = collect();
    foreach ($level5Referrals as $level5User) {
        $level6Referrals = $level6Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level5User->pass])
        );
    }

    
    $level7Referrals = collect();
    foreach ($level6Referrals as $level6User) {
        $level7Referrals = $level7Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level6User->pass])
        );
    }

    
    $level8Referrals = collect();
    foreach ($level7Referrals as $level7User) {
        $level8Referrals = $level8Referrals->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level7User->pass])
        );
    }

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    for ($level = 1; $level <= 7; $level++) {
        $this->assertTrue(isset($response['referrals']['level_' . $level]));
    }

    
    $this->assertFalse(array_key_exists('level_8', $response['referrals']));

});

it('returns levels 1 to 7 referrals and calculates lowlevelrefs as the sum of levels 2 to 7', function () {
    
    $user = User::factory()->create([
        'points' => 100,
    ]);

    
    $level1Users = User::factory()->count(3)->create(['referrer_pass' => $user->pass]);

    
    $level2Users = collect();
    foreach ($level1Users as $level1User) {
        $level2Users = $level2Users->merge(
            User::factory()->count(2)->create(['referrer_pass' => $level1User->pass])
        );
    }

    
    $currentLevelUsers = $level2Users;
    $totalLowLevelRefs = $level2Users->count(); 

    for ($level = 3; $level <= 7; $level++) {
        $currentLevelUsers = $currentLevelUsers->flatMap(function ($user) use ($level) {
            return User::factory()->count(2)->create(['referrer_pass' => $user->pass]);
        });
        $totalLowLevelRefs += $currentLevelUsers->count(); 
    }

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/data');

    
    $response->assertStatus(200);

    
    for ($level = 1; $level <= 7; $level++) {
        $this->assertTrue(isset($response['referrals']['level_' . $level]));
    }

    
    $this->assertEquals($totalLowLevelRefs, $response['lowlevelrefs']);

    
    $this->assertFalse(array_key_exists('level_8', $response['referrals']));

    
    $this->assertEquals(3, $response['referrals']['level_1']); 
    $this->assertEquals(6, $response['referrals']['level_2']); 
    
});


it('returns the user language and updates it', function () {
    
    $userData = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'language' => null, 
    ];

    
    $response = $this->postJson('/api/register', $userData);

    
    $response->assertStatus(201);

    $user = User::findOrFail($response['user']['id']);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user');

    
    $this->assertEquals('en', $response['language']);

    
    $updateData = ['language' => 'es'];

    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(200);

    
    $response = $this->getJson('/api/user');

    
    $this->assertEquals('es', $response['language']);
});

it('retrieves the authenticated user', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $response = $this->getJson('/api/user');

    
    $response->assertStatus(200);

    
    $response->assertJson([
        'id' => $this->user->id,
        'name' => $this->user->name,
        'email' => $this->user->email,
        'language' => 'en',
    ]);
});

it('updates the authenticated user language', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['language' => 'es'];

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(200);

    
    $this->user->refresh();

    
    expect($this->user->language)->toBe('es');
});

it('returns validation errors when updating with invalid data', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['language' => '']; 

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(422);
});

it('updates the authenticated user name', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['name' => 'Updated User'];

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(200);

    
    $this->user->refresh();

    
    expect($this->user->name)->toBe('Updated User');
});

it('returns validation errors when updating email with existing email', function () {
    
    $anotherUser = User::factory()->create([
        'email' => 'otheruser@example.com',
    ]);

    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['email' => 'otheruser@example.com'];

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(422);
});

it('returns validation errors when updating with invalid email', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['email' => 'invalid-email'];

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(422);
});

it('returns validation errors when updating with invalid password', function () {
    
    Sanctum::actingAs($this->user, ['*']);

    
    $updateData = ['password' => 'short'];

    
    $response = $this->putJson('/api/user', $updateData);

    
    $response->assertStatus(422);
});

it('retrieves the authenticated user data', function () {
    
    $user = User::factory()->create([
        'language' => 'es', 
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user');

    
    $response->assertStatus(200);

    
    $response->assertJson([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'language' => 'es', 
    ]);
});

it('retrieves the profile of the authenticated user with avatar from initials', function () {
    
    $user = User::factory()->create([
        'name' => 'Prof. Cristal Heathcote II',
        'email' => 'abraham58@example.com',
        'language' => 'es',
        'profile_photo_path' => null, 
    ]);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user');

    
    $response->assertStatus(200);

    
    $response->assertJsonFragment([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'language' => 'es',
    ]);

    
    $nameParts = explode(' ', $user->name);
    $initials = implode('+', array_map(fn($part) => strtoupper(substr($part, 0, 1)), $nameParts));

    
    $expectedAvatarUrl = 'https://ui-avatars.com/api/?name=' . $initials . '&color=7F9CF5&background=EBF4FF';
    $this->assertEquals($expectedAvatarUrl, $response['profile_photo_url']);
});

