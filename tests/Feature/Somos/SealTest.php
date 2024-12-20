<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Seal;
use App\Models\Category;
use App\Models\Commerce;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('seals/default.svg', 'default image content');
});

it('creates a seal with an auto-generated slug', function () {
    $seal = Seal::factory()->create(['name' => 'New Seal', 'slug' => null]);

    $this->assertDatabaseHas('seals', [
        'name' => 'New Seal',
        'slug' => 'new-seal',
    ]);
});

it('creates a seal with a custom slug', function () {
    $seal = Seal::factory()->create([
        'name' => 'Another Seal',
        'slug' => 'custom-slug',
    ]);

    $this->assertDatabaseHas('seals', [
        'name' => 'Another Seal',
        'slug' => 'custom-slug',
    ]);
});

it('creates a seal without an image and uses the default image', function () {
    $seal = Seal::factory()->create(['image' => null]);

    $this->assertNotNull($seal->image);
    $this->assertEquals('seals/default/::STATE::.svg', $seal->image);
});

it('creates a seal with an image', function () {
    $image = UploadedFile::fake()->image('seal.svg');

    
    $seal = Seal::factory()->create();

    
    Storage::disk('public')->put("seals/{$seal->id}/full.svg", $image->getContent());

    
    Storage::disk('public')->assertExists("seals/{$seal->id}/full.svg");

    
    $this->assertEquals("seals/{$seal->id}/::STATE::.svg", $seal->image);
});

it('associates a seal with a commerce', function () {
    $seal = Seal::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->seals()->attach($seal->id, ['all' => false]);

    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'all' => false,
    ]);

    $this->assertTrue($commerce->seals->contains($seal));
});

it('associates a seal with a commerce with a state', function () {
    $seal = Seal::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->seals()->attach($seal->id, ['all' => false]);
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'all' => false,
    ]);

    $commerce->seals()->updateExistingPivot($seal->id, ['all' => true]);
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'all' => true,
    ]);
});

it('associates a seal with multiple commerces', function () {
    $seal = Seal::factory()->create();
    $commerces = Commerce::factory()->count(3)->create();

    $commerces->each(function ($commerce) use ($seal) {
        $commerce->seals()->attach($seal->id, ['all' => false]);
    });

    $this->assertCount(3, $seal->commerces);
});

it('associates a commerce with multiple seals', function () {
    $commerce = Commerce::factory()->create();
    $seals = Seal::factory()->count(3)->create();

    $seals->each(function ($seal) use ($commerce) {
        $commerce->seals()->attach($seal->id, ['all' => false]);
    });

    $this->assertCount(3, $commerce->seals);
});

it('removes commerce associations when a seal is deleted', function () {
    $seal = Seal::factory()->create();
    $commerces = Commerce::factory()->count(2)->create();

    $commerces->each(function ($commerce) use ($seal) {
        $commerce->seals()->attach($seal->id, ['all' => false]);
    });

    $seal->delete();

    foreach ($commerces as $commerce) {
        $this->assertDatabaseMissing('commerce_seal', [
            'commerce_id' => $commerce->id,
            'seal_id' => $seal->id,
        ]);
    }
});

it('removes seal associations when a commerce is deleted', function () {
    $commerce = Commerce::factory()->create();
    $seals = Seal::factory()->count(2)->create();

    $seals->each(function ($seal) use ($commerce) {
        $commerce->seals()->attach($seal->id, ['all' => false]);
    });

    $commerce->delete();

    foreach ($seals as $seal) {
        $this->assertDatabaseMissing('commerce_seal', [
            'commerce_id' => $commerce->id,
            'seal_id' => $seal->id,
        ]);
    }
});

it('a seal uses a default image if none is provided', function () {
    
    $seal = Seal::factory()->create(['image' => null]);

    
    $defaultImagePath = 'seals/default/::STATE::.svg';

    
    $this->assertEquals($defaultImagePath, $seal->image);
});

it('ensures unique slug generation', function () {
    $seal1 = Seal::factory()->create(['name' => 'Unique Seal']);
    $seal2 = Seal::factory()->create(['name' => 'Unique Seal']);

    $this->assertNotEquals($seal1->slug, $seal2->slug);
});

it('keeps the slug unchanged when the name is updated', function () {
    $seal = Seal::factory()->create(['name' => 'Original Name']);

    $originalSlug = $seal->slug;

    $seal->update(['name' => 'Updated Name']);

    $this->assertEquals($originalSlug, $seal->slug);
});

it('prevents seal creation without a name', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Seal::factory()->create(['name' => null]);
});

it('stores seal image in the correct directory', function () {
    $image = UploadedFile::fake()->image('seal.svg');

    $seal = Seal::factory()->create(['image' => $image]);

    Storage::disk('public')->assertExists("seals/{$seal->id}/{$image->hashName()}");
});

it('handles all states for commerce-seal association', function () {
    $seal = Seal::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->seals()->attach($seal->id, ['state' => 0]);
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'state' => 0,
    ]);

    $commerce->seals()->updateExistingPivot($seal->id, ['state' => 1]);
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'state' => 1,
    ]);

    $commerce->seals()->updateExistingPivot($seal->id, ['state' => 2]);
    $this->assertDatabaseHas('commerce_seal', [
        'commerce_id' => $commerce->id,
        'seal_id' => $seal->id,
        'state' => 2,
    ]);
});


