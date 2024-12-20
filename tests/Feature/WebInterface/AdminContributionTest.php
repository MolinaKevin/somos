<?php

namespace Tests\Feature\WebInterface;

use App\Models\Contribution;
use App\Models\Somos;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContributionTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_authenticated_user_can_see_contributions_list()
    {
        
        $user = User::factory()->create();

        
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);

        
        Contribution::factory()->count(3)->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 100,
        ]);

        
        $this->actingAs($user);

        
        $response = $this->get('/admin/contributions');

        
        $response->assertStatus(200);

        
        $response->assertSee('100 puntos');
        $response->assertSee('Test Somos');
        $response->assertSee('Test NRO');
    }

    
    public function test_can_create_a_contribution()
    {
        
        $admin = User::factory()->create();
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);

        
        $this->actingAs($admin);

        
        $contributionData = [
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.123,
        ];

        
        $response = $this->post('/admin/contributions', $contributionData);

        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        
        $this->assertDatabaseHas('contributions', [
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.123,
        ]);
    }

    
    public function test_can_view_a_contribution_detail()
    {
        
        $admin = User::factory()->create();
        
        
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200,
        ]);

        
        $this->actingAs($admin);

        
        $response = $this->get("/admin/contributions/{$contribution->id}");

        
        $response->assertStatus(200);

        
        $response->assertSee('200 puntos');
        $response->assertSee('Test Somos');
        $response->assertSee('Test NRO');
    }

    
    public function test_can_update_a_contribution()
    {
        
        $admin = User::factory()->create();

        
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 100,
        ]);

        
        $updatedData = [
            'somos_id' => $somos->id, 
            'nro_id' => $nro->id,     
            'points' => 200.456,
        ];

        
        $this->actingAs($admin);

        
        $response = $this->put("/admin/contributions/{$contribution->id}", $updatedData);

        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        
        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.456,
        ]);
    }

    
    public function test_can_delete_a_contribution()
    {
        
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.456,
        ]);

        
        $adminUser = User::factory()->create();

        
        $response = $this->actingAs($adminUser)->delete("/admin/contributions/{$contribution->id}");

        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        
        $this->assertDatabaseMissing('contributions', [
            'id' => $contribution->id,
        ]);
    }
}

