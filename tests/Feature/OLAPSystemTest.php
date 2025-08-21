<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OLAPSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Crear datos de prueba
        $sucursal = Sucursal::factory()->create();
        $producto = Producto::factory()->create(['sucursal_id' => $sucursal->id]);
        Inventario::factory()->create([
            'sucursal_id' => $sucursal->id,
            'producto_id' => $producto->id
        ]);
    }

    public function test_user_can_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    public function test_user_can_view_dashboard()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->getJson('/api/dashboard-metrics');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'ventasHoy', 
                     'ventasMes', 
                     'gananciaTotal',
                     'productosVendidos'
                 ]);
    }

    public function test_user_can_view_sucursales()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->getJson('/api/sucursales');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_sucursal()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->postJson('/api/sucursales', [
            'nombre' => 'Sucursal Test',
            'direccion' => 'Calle Test 123',
            'ciudad' => 'Ciudad Test',
            'pais' => 'País Test',
            'activa' => true
        ]);
        
        $response->assertStatus(201)
                 ->assertJson(['nombre' => 'Sucursal Test']);
                
        $this->assertDatabaseHas('sucursals', ['nombre' => 'Sucursal Test']);
    }

    public function test_user_can_view_productos()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->getJson('/api/productos');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_can_view_inventario()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->getJson('/api/inventario');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_olap_cube_query()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->postJson('/api/olap/cube', [
            'dimensions' => ['sucursal', 'producto'],
            'measures' => ['ventas', 'ganancia'],
            'filters' => []
        ]);
        
        $response->assertStatus(200);
    }
}