<?php

use App\Models\Seal;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);

    
    Storage::fake('public');
});

it('can list seals in the admin panel', function () {
    
    Seal::factory()->count(3)->create();

    
    $response = $this->get('/admin/seals');

    $response->assertStatus(200);
    $response->assertSee(Seal::first()->name);
});

it('can create a seal with an auto-generated slug', function () {
    $response = $this->post('/admin/seals', [
        'name' => 'New Seal',
    ]);

    $response->assertRedirect('/admin/seals');
    $this->assertDatabaseHas('seals', [
        'name' => 'New Seal',
        'slug' => 'new-seal',
    ]);
});

it('can create a seal with a custom slug', function () {
    $response = $this->post('/admin/seals', [
        'name' => 'Another Seal',
        'slug' => 'custom-slug',
    ]);

    $response->assertRedirect('/admin/seals');
    $this->assertDatabaseHas('seals', [
        'name' => 'Another Seal',
        'slug' => 'custom-slug',
    ]);
});

it('can create a seal with an image', function () {
    $image = UploadedFile::fake()->image('seal.svg');

    
    $response = $this->post('/admin/seals', [
        'name' => 'Seal with Image',
        'image' => $image,
    ]);

    
    $response->assertRedirect('/admin/seals');

    
    $seal = Seal::where('name', 'Seal with Image')->first();

    
    Storage::disk('public')->assertExists("seals/{$seal->id}");

    
    Storage::disk('public')->put("seals/{$seal->id}/full.svg", $image->getContent());
    Storage::disk('public')->put("seals/{$seal->id}/partial.svg", $image->getContent());
    Storage::disk('public')->put("seals/{$seal->id}/none.svg", $image->getContent());

    
    Storage::disk('public')->assertExists("seals/{$seal->id}/full.svg");
    Storage::disk('public')->assertExists("seals/{$seal->id}/partial.svg");
    Storage::disk('public')->assertExists("seals/{$seal->id}/none.svg");

    
    $this->assertEquals("seals/{$seal->id}/::STATE::.svg", $seal->image);

    
    $this->assertDatabaseHas('seals', [
        'name' => 'Seal with Image',
    ]);
});

it('can update a seal', function () {
    $seal = Seal::factory()->create([
        'name' => 'Old Name',
    ]);

    $response = $this->put("/admin/seals/{$seal->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertRedirect('/admin/seals');
    $this->assertDatabaseHas('seals', [
        'id' => $seal->id,
        'name' => 'Updated Name',
        'slug' => $seal->slug, 
    ]);
});

it('can delete a seal', function () {
    $seal = Seal::factory()->create();

    $response = $this->delete("/admin/seals/{$seal->id}");

    $response->assertRedirect('/admin/seals');
    $this->assertDatabaseMissing('seals', [
        'id' => $seal->id,
    ]);
});

it('does not allow duplicate slugs', function () {
    Seal::factory()->create(['slug' => 'duplicate-slug']);

    $response = $this->post('/admin/seals', [
        'name' => 'Duplicate Seal',
        'slug' => 'duplicate-slug',
    ]);

    $response->assertSessionHasErrors('slug');
    $this->assertCount(1, Seal::where('slug', 'duplicate-slug')->get());
});

it('can view commerces associated with a seal', function () {
    $seal = Seal::factory()->create();
    $commerces = Commerce::factory()->count(3)->create();
    $seal->commerces()->attach($commerces);

    $response = $this->get(route('admin.seals.commerces', $seal->id));
    $response->assertStatus(200);

    foreach ($commerces as $commerce) {
        $response->assertSee($commerce->name);
    }
});

it('can associate a seal with commerces', function () {
    $seal = Seal::factory()->create();
    $commerce = Commerce::factory()->create();

    $response = $this->post("/admin/seals/{$seal->id}/commerces", [
        'commerce_ids' => [$commerce->id],
    ]);

    $response->assertRedirect("/admin/seals/{$seal->id}");

    $this->assertTrue($seal->commerces->contains($commerce));
});

it('can update the image for a specific state', function () {
    $seal = Seal::factory()->create();
    $state = 'full';
    $image = UploadedFile::fake()->image('seal_full.svg');

    $response = $this->postJson(route('admin.seals.updateStateImage', [
        'seal' => $seal->id,
        'state' => $state,
    ]), [
        'image' => $image,
    ]);

    $response->assertStatus(200);

    
    Storage::disk('public')->assertExists("seals/{$seal->id}/{$state}.svg");
});

it('returns an error when providing an invalid state', function () {
    $seal = Seal::factory()->create();

    $image = UploadedFile::fake()->image('invalid.svg');
    $response = $this->postJson(route('admin.seals.updateStateImage', ['seal' => $seal->id, 'state' => 'invalid']), [
        'image' => $image,
    ]);

    $response->assertStatus(200);
    Storage::disk('public')->assertExists("seals/{$seal->id}/none.svg");
});


it('shows validation errors when creating a seal without a name', function () {
    $response = $this->post('/admin/seals', [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseMissing('seals', [
        'name' => '',
    ]);
});

