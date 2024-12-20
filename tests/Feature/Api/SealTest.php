<?php

use App\Models\Seal;
use App\Enums\SealState;
use App\Models\L10n;
use App\Models\Commerce;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Cache::flush();
    uses(RefreshDatabase::class);
});

afterEach(function () {
    Cache::flush(); 
});

it('fetches all seals with user locale translations', function () {
    
    $seals = Seal::factory()->count(3)->create();

    
    foreach ($seals as $seal) {
        L10n::factory()->create([
            'locale' => 'es',
            'group' => 'seal',
            'key' => $seal->slug,
            'value' => $seal->name . ' (ES)',
        ]);
        L10n::factory()->create([
            'locale' => 'en',
            'group' => 'seal',
            'key' => $seal->slug,
            'value' => $seal->name . ' (EN)',
        ]);
    }

    $user = User::factory()->create(['language' => 'es']);

    $response = $this->actingAs($user)->getJson('/api/seals');

    
    $response->assertStatus(200);
    $response->assertJsonFragment(['translated_name' => $seals[0]->name . ' (ES)']);
    $response->assertJsonFragment(['translated_name' => $seals[1]->name . ' (ES)']);
    $response->assertJsonFragment(['translated_name' => $seals[2]->name . ' (ES)']);
});

it('fetches all seals in English when no user is authenticated', function () {
    
    $seals = Seal::factory()->count(3)->create();

    
    foreach ($seals as $seal) {
        L10n::factory()->create([
            'locale' => 'en',
            'group' => 'seal',
            'key' => $seal->slug,
            'value' => $seal->name . ' (EN)',
        ]);
    }

    
    $response = $this->get('/api/seals');

    
    $response->assertStatus(200);
    $response->assertJsonFragment(['translated_name' => $seals[0]->name . ' (EN)']);
    $response->assertJsonFragment(['translated_name' => $seals[1]->name . ' (EN)']);
    $response->assertJsonFragment(['translated_name' => $seals[2]->name . ' (EN)']);
});

it('fetches commerces from a seal', function () {
    
    $seal = Seal::factory()->create();

    
    $commerces = Commerce::factory()->count(3)->create();

    
    $seal->commerces()->attach($commerces);

    
    $response = $this->getJson("/api/seals/{$seal->id}/commerces");

    
    $response->assertStatus(200);
    foreach ($commerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});

it('updates a commerce with an array of seals', function () {
    $commerce = Commerce::factory()->create();
    $seals = Seal::factory()->count(3)->create();

    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $data = [
        'name' => 'Updated Commerce',
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 0],
            ['id' => $seals[1]->id, 'state' => 1],
            ['id' => $seals[2]->id, 'state' => 2],
        ],
    ];

    $response = $this->putJson("/api/user/commerces/{$commerce->id}", $data);

    $response->assertStatus(200);

    foreach ($data['seals'] as $seal) {
        $this->assertDatabaseHas('commerce_seal', [
            'commerce_id' => $commerce->id,
            'seal_id' => $seal['id'],
            'state' => $seal['state'],
        ]);
    }
});

it('fetches a commerce with its associated seals', function () {
    
    $commerce = Commerce::factory()->create();

    
    $seals = Seal::factory()->count(3)->create();
    $commerce->seals()->attach($seals);

    
    $user = User::factory()->create();
    $commerce->users()->attach($user);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}");

    
    $response->assertStatus(200);

    
    $response->assertJsonFragment([
        'id' => $commerce->id,
        'seal_ids' => $seals->pluck('id')->toArray(),
    ]);
});

it('updates a commerce with seals and their states', function () {
    $commerce = Commerce::factory()->create();
    $seals = Seal::factory()->count(3)->create();

    
    $data = [
        'name' => $commerce->name,
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 0],
            ['id' => $seals[1]->id, 'state' => 1],
            ['id' => $seals[2]->id, 'state' => 2],
        ],
    ];

    $user = User::factory()->create();
    $commerce->users()->attach($user);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/user/commerces/{$commerce->id}", $data);

    $response->assertStatus(200);

    
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seals[0]->id,
        'state' => 0,
    ]);

    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seals[1]->id,
        'state' => 1,
    ]);

    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seals[2]->id,
        'state' => 2,
    ]);
});



it('fetches a commerce with its associated seals and states', function () {
    $commerce = Commerce::factory()->create();

    $seals = Seal::factory()->count(3)->create();

    $commerce->seals()->attach($seals[0]->id, ['state' => 0]);
    $commerce->seals()->attach($seals[1]->id, ['state' => 1]);
    $commerce->seals()->attach($seals[2]->id, ['state' => 2]);

    $user = User::factory()->create();
    $commerce->users()->attach($user);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/commerces/{$commerce->id}");

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $commerce->id,
        'seals_with_state' => [
            ['id' => $seals[0]->id, 'state' => 'none'],
            ['id' => $seals[1]->id, 'state' => 'partial'],
            ['id' => $seals[2]->id, 'state' => 'full'],
        ],
    ]);
});

it('fetches commerces filtered by categories and seals', function () {
    $categories = Category::factory()->count(5)->create();
    $seals = Seal::factory()->count(3)->create();

    $commerce1 = Commerce::factory()->create();
    $commerce1->categories()->attach([$categories[0]->id, $categories[1]->id]);
    $commerce1->seals()->attach($seals[0]->id, ['state' => 1]);
    $commerce1->seals()->attach($seals[1]->id, ['state' => 2]);

    $commerce2 = Commerce::factory()->create();
    $commerce2->categories()->attach([$categories[2]->id, $categories[3]->id]);
    $commerce2->seals()->attach($seals[0]->id, ['state' => 1]);
    $commerce2->seals()->attach($seals[2]->id, ['state' => 2]);

    $commerce3 = Commerce::factory()->create();
    $commerce3->categories()->attach([$categories[4]->id]);
    $commerce3->seals()->attach($seals[2]->id, ['state' => 0]);

    $filterData = [
        'category_ids' => [$categories[0]->id, $categories[1]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 1],
            ['id' => $seals[1]->id, 'state' => 2],
        ],
    ];

    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $commerce1->id]);
    $response->assertJsonMissing(['id' => $commerce2->id]);
    $response->assertJsonMissing(['id' => $commerce3->id]);
});

it('returns no commerces if filters do not match any commerce', function () {
    
    $categories = Category::factory()->count(3)->create();

    
    $seals = Seal::factory()->count(3)->create();

    
    Commerce::factory()->count(3)->create();

    
    $filterData = [
        'category_ids' => [$categories[0]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 0],
        ],
    ];

    
    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    
    $response->assertStatus(200);

    
    $response->assertJsonCount(0, 'data');
});

it('validates the filters sent to the endpoint', function () {
    
    $filterData = [
        'category_ids' => ['invalid'], 
        'seals' => [
            ['id' => 'invalid', 'state' => 'unknown'], 
        ],
    ];

    
    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    
    $response->assertStatus(422);

    
    $response->assertJsonValidationErrors([
        'category_ids.0',
        'seals.0.id',
        'seals.0.state',
    ]);
});

it('fetches commerces when filtering by seals with broader matching logic', function () {
    $categories = Category::factory()->count(3)->create();
    $seals = Seal::factory()->count(2)->create();


    $commerce1 = Commerce::factory()->create();

    $commerce1->categories()->attach([$categories[0]->id]);
    $commerce1->seals()->attach($seals[0]->id, ['state' => SealState::FULL->value]); 
    $commerce1->seals()->attach($seals[1]->id, ['state' => SealState::NONE->value]);

    $commerce2 = Commerce::factory()->create();
    $commerce2->categories()->attach([$categories[1]->id]);
    $commerce2->seals()->attach($seals[0]->id, ['state' => SealState::PARTIAL->value]); 
    $commerce2->seals()->attach($seals[1]->id, ['state' => SealState::PARTIAL->value]); 

    $commerce3 = Commerce::factory()->create();
    $commerce3->categories()->attach([$categories[2]->id]);
    $commerce3->seals()->attach($seals[0]->id, ['state' => SealState::NONE->value]); 
    $commerce3->seals()->attach($seals[1]->id, ['state' => SealState::NONE->value]); 

    $filterData = [
        'category_ids' => [$categories[0]->id, $categories[1]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => SealState::PARTIAL->value], 
        ],
    ];

    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    $response->assertStatus(200);

    
    $response->assertJsonFragment(['id' => $commerce1->id]);

    
    $response->assertJsonFragment(['id' => $commerce2->id]);

    
    $response->assertJsonMissing(['id' => $commerce3->id]);
});

it('fetches commerces when filtering by seals with broader matching logic number 2', function () {
    $categories = Category::factory()->count(3)->create();
    $seals = Seal::factory()->count(2)->create();


    $commerce1 = Commerce::factory()->create();

    $commerce1->categories()->attach([$categories[0]->id]);
    $commerce1->seals()->attach($seals[0]->id, ['state' => SealState::FULL->value]); 
    $commerce1->seals()->attach($seals[1]->id, ['state' => SealState::NONE->value]);

    $commerce2 = Commerce::factory()->create();
    $commerce2->categories()->attach([$categories[0]->id]);
    $commerce2->seals()->attach($seals[0]->id, ['state' => SealState::PARTIAL->value]); 
    $commerce2->seals()->attach($seals[1]->id, ['state' => SealState::PARTIAL->value]); 

    $commerce3 = Commerce::factory()->create();
    $commerce3->categories()->attach([$categories[2]->id]);
    $commerce3->seals()->attach($seals[0]->id, ['state' => SealState::NONE->value]); 
    $commerce3->seals()->attach($seals[1]->id, ['state' => SealState::NONE->value]); 

    $filterData = [
        'category_ids' => [$categories[0]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => SealState::PARTIAL->value], 
        ],
    ];

    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    $response->assertStatus(200);

    
    $response->assertJsonFragment(['id' => $commerce1->id]);

    
    $response->assertJsonFragment(['id' => $commerce2->id]);

    
    $response->assertJsonMissing(['id' => $commerce3->id]);
});

it('normalizes seal states from names or integers in filter-by-filters', function () {
    $categories = Category::factory()->count(2)->create();
    $seals = Seal::factory()->count(2)->create();

    $commerce1 = Commerce::factory()->create();
    $commerce1->categories()->attach([$categories[0]->id]);
    $commerce1->seals()->attach($seals[0]->id, ['state' => 0]); 

    $commerce2 = Commerce::factory()->create();
    $commerce2->categories()->attach([$categories[0]->id]);
    $commerce2->seals()->attach($seals[0]->id, ['state' => 1]); 
    $commerce2->seals()->attach($seals[1]->id, ['state' => 2]); 

    $filterData = [
        'category_ids' => [$categories[0]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 'partial'], 
            ['id' => $seals[1]->id, 'state' => 2], 
        ],
    ];

    $response = $this->postJson('/api/commerces/filter-by-filters', $filterData);

    $response->assertStatus(200);

    
    $response->assertJsonMissing(['id' => $commerce1->id]);

    
    $response->assertJsonFragment(['id' => $commerce2->id]);
});

it('fetches seals with correct base image URLs', function () {
    uses(RefreshDatabase::class);
    Storage::fake('public');

    
    $seals = Seal::factory()->count(3)->create();

    
    Storage::disk('public')->makeDirectory("seals/{$seals[0]->id}");
    Storage::disk('public')->put("seals/{$seals[0]->id}/full.svg", 'specific full image');

    Storage::disk('public')->makeDirectory("seals/{$seals[1]->id}");
    Storage::disk('public')->put("seals/{$seals[1]->id}/partial.svg", 'specific partial image');

    Storage::disk('public')->makeDirectory("seals/default");
    Storage::disk('public')->put("seals/default/none.svg", 'default none image');

    
    $response = $this->getJson('/api/seals');

    
    $response->assertStatus(200);

    
    $response->assertJsonFragment([
        'id' => $seals[0]->id,
        'image' => "seals/{$seals[0]->id}/::STATE::.svg",
    ]);

    $response->assertJsonFragment([
        'id' => $seals[1]->id,
        'image' => "seals/{$seals[1]->id}/::STATE::.svg",
    ]);

    $response->assertJsonFragment([
        'id' => $seals[2]->id,
        'image' => "seals/default/::STATE::.svg",
    ]);
});

it('normalizes seal states when saving a commerce', function () {
    
    $categories = Category::factory()->count(2)->create();
    $seals = Seal::factory()->count(3)->create();

    $user = User::factory()->create(['language' => 'es']);

    $response = $this->actingAs($user)->getJson('/api/seals');

    
    $commerceData = [
        'name' => 'Test Commerce',
        'address' => 'Test Address',
        'city' => 'Test City',
        'plz' => '12345',
        'latitude' => '51.53636134',
        'longitude' => '9.91903678',
        'categories' => [$categories[0]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 'partial'], 
            ['id' => $seals[1]->id, 'state' => 2], 
            ['id' => $seals[2]->id, 'state' => 'none'], 
        ],
    ];

    
    $response = $this->postJson('/api/user/commerces', $commerceData);

    
    $response->assertStatus(201);

    
    $commerce = Commerce::where('name', 'Test Commerce')->first();

    
    expect($commerce)->not->toBeNull();

    
    expect($commerce->categories->pluck('id')->toArray())->toContain($categories[0]->id);

    
    $sealsWithStates = $commerce->seals_with_state;

    expect($sealsWithStates)->toMatchArray([
        ['id' => $seals[0]->id, 'state' => 'partial'], 
        ['id' => $seals[1]->id, 'state' => 'full'], 
        ['id' => $seals[2]->id, 'state' => 'none'], 
    ]);
});

it('normalizes seal states when updating a commerce', function () {
    
    $categories = Category::factory()->count(2)->create();
    $seals = Seal::factory()->count(3)->create();

    
    $user = User::factory()->create(['language' => 'es']);

    
    $initialCommerceData = [
        'name' => 'Initial Commerce',
        'address' => 'Initial Address',
        'city' => 'Initial City',
        'plz' => '12345',
        'latitude' => '51.53636134',
        'longitude' => '9.91903678',
        'categories' => [$categories[0]->id],
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 'none'], 
            ['id' => $seals[1]->id, 'state' => 'partial'], 
        ],
    ];

    
    $response = $this->actingAs($user)->postJson('/api/user/commerces', $initialCommerceData);

    
    $response->assertStatus(201);

    
    $commerce = Commerce::where('name', 'Initial Commerce')->first();

    
    expect($commerce)->not->toBeNull();

    
    $updatedCommerceData = [
        'name' => 'Updated Commerce',
        'seals' => [
            ['id' => $seals[0]->id, 'state' => 'partial'], 
            ['id' => $seals[1]->id, 'state' => 2], 
            ['id' => $seals[2]->id, 'state' => 'none'], 
        ],
    ];

    
    $response = $this->actingAs($user)->putJson("/api/user/commerces/{$commerce->id}", $updatedCommerceData);

    
    $response->assertStatus(200);

    
    $updatedCommerce = Commerce::find($commerce->id);

    
    expect($updatedCommerce->name)->toBe('Updated Commerce');

    
    $updatedSealsWithStates = $updatedCommerce->seals_with_state;

    expect($updatedSealsWithStates)->toMatchArray([
        ['id' => $seals[0]->id, 'state' => 'partial'], 
        ['id' => $seals[1]->id, 'state' => 'full'], 
        ['id' => $seals[2]->id, 'state' => 'none'], 
    ]);
});

