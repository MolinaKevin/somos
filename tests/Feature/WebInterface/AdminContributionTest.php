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

    // Test: Autenticado puede ver la lista de contribuciones
    public function test_authenticated_user_can_see_contributions_list()
    {
        // Crear un usuario y autenticarlo
        $user = User::factory()->create();

        // Crear una entidad Somos y una NRO
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);

        // Crear algunas contribuciones
        Contribution::factory()->count(3)->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 100,
        ]);

        // Actuar como el usuario autenticado
        $this->actingAs($user);

        // Acceder a la página de administrar contribuciones
        $response = $this->get('/admin/contributions');

        // Verificar que la página carga correctamente
        $response->assertStatus(200);

        // Verificar que las contribuciones están en la página
        $response->assertSee('100 puntos');
        $response->assertSee('Test Somos');
        $response->assertSee('Test NRO');
    }

    // Test: Crear Contribución
    public function test_can_create_a_contribution()
    {
        // Crear un usuario administrador y autenticarlo
        $admin = User::factory()->create();
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);

        // Actuar como el usuario autenticado
        $this->actingAs($admin);

        // Datos de la contribución
        $contributionData = [
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.123,
        ];

        // Enviar petición para crear una contribución
        $response = $this->post('/admin/contributions', $contributionData);

        // Verificar que la respuesta sea un redirect a la lista de contribuciones
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        // Verificar que la contribución fue creada en la base de datos
        $this->assertDatabaseHas('contributions', [
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.123,
        ]);
    }

    // Test: Ver detalle de contribución
    public function test_can_view_a_contribution_detail()
    {
        // Crear un usuario administrador y autenticarlo
        $admin = User::factory()->create();
        
        // Crear una entidad Somos, una NRO y una contribución
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200,
        ]);

        // Actuar como el usuario autenticado
        $this->actingAs($admin);

        // Acceder a la página de detalle de la contribución
        $response = $this->get("/admin/contributions/{$contribution->id}");

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Verificar que los detalles de la contribución están presentes
        $response->assertSee('200 puntos');
        $response->assertSee('Test Somos');
        $response->assertSee('Test NRO');
    }

    // Test: Actualizar Contribución
    public function test_can_update_a_contribution()
    {
        // Crear un usuario administrador
        $admin = User::factory()->create();

        // Crear una entidad Somos, una NRO y una contribución
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 100,
        ]);

        // Datos actualizados para la contribución
        $updatedData = [
            'somos_id' => $somos->id, // Asegurarse de enviar somos_id
            'nro_id' => $nro->id,     // Asegurarse de enviar nro_id
            'points' => 200.456,
        ];

        // Autenticar al usuario administrador
        $this->actingAs($admin);

        // Enviar la solicitud para actualizar la contribución
        $response = $this->put("/admin/contributions/{$contribution->id}", $updatedData);

        // Verificar que la respuesta sea un redirect a la lista de contribuciones
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        // Verificar que los datos fueron actualizados en la base de datos
        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.456,
        ]);
    }

    // Test: Eliminar Contribución
    public function test_can_delete_a_contribution()
    {
        // Crear una entidad Somos, una NRO y una contribución
        $somos = Somos::factory()->create(['name' => 'Test Somos']);
        $nro = Nro::factory()->create(['name' => 'Test NRO']);
        $contribution = Contribution::factory()->create([
            'somos_id' => $somos->id,
            'nro_id' => $nro->id,
            'points' => 200.456,
        ]);

        // Crear un usuario con permisos para eliminar
        $adminUser = User::factory()->create();

        // Actuar como el usuario autenticado
        $response = $this->actingAs($adminUser)->delete("/admin/contributions/{$contribution->id}");

        // Verificar que la respuesta sea un redirect a la lista de contribuciones
        $response->assertStatus(302);
        $response->assertRedirect('/admin/contributions');

        // Verificar que la contribución fue eliminada de la base de datos
        $this->assertDatabaseMissing('contributions', [
            'id' => $contribution->id,
        ]);
    }
}

