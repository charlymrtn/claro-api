<?php

namespace Tests\Unit\App\Http\Controllers\API\v1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Http\Controllers\API\v1\ClienteController;

class ClienteControllerTest extends TestCase
{

    use DatabaseTransactions;
    protected $mCliente;
    protected $base_url;
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        // Inicia mock y lo asígna a la instancia
        $this->mCliente = Mockery::mock(Eloquent::class, Cliente::class);
        $this->app->instance(Cliente::class, $this->mCliente);
        // Variables
        $this->base_url = '/v1/cliente';
        $this->controller = new ClienteController(new Cliente());
        // Crea usuario para autenticacion
        $this->user = factory(User::class)->make();
    }

    public function tearDown()
    {
        // Quita los mocks al finalizar
        Mockery::close();
        parent::tearDown();
    }

    public function test_index()
    {
        // Variables
        $oCliente = factory(Cliente::class, 5)->make();

        // Prueba seguridad
        $this->json('GET', $this->base_url)
            ->assertStatus(401);

        // Prueba la validacion
        $this->withoutMiddleware()->actingAs($this->user)->json('GET', $this->base_url, ['per_page' => 'algo'])
            ->assertStatus(400)
            ->assertJson(["status" => "fail", "data" => ["errors" => true]]);

        // Prueba el camino exitoso
        $this->mCliente
            ->shouldReceive('where')
            ->with(Mockery::on(function ($where) {
                // Mockea todas las posibles llamadas al clause
                $mockDb = Mockery::mock('Illuminate\Database\DatabaseManager');
                $subQuery = $mockDb
                    ->shouldReceive('orWhere')->with('uuid', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('comercio_uuid', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('nombre', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('apellido_paterno', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('apellido_materno', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('sexo', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('email', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('nacimiento', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('estado', 'like', "%algo%")->andReturnSelf()
                    ->getMock();
                $where($subQuery);
                return true;
            }))->once()->andReturnSelf()
            ->shouldReceive('orderBy')->once()->andReturnSelf()
            ->shouldReceive('paginate')->withArgs([25])->andReturn($oCliente);
        $this->withoutMiddleware()->json('GET', $this->base_url, ['search' => 'algo'])
            ->assertStatus(200)
            ->assertJson(['status' => "success", 'data' => ['clientes' => true]]);

        // Prueba la excepción
        $this->mCliente
            ->shouldReceive('where')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
        $this->withoutMiddleware()->json('GET', $this->base_url)
            ->assertStatus(500)
            ->assertJson(['status' => 'error', 'error' => true]);
    }
//
    public function test_show()
    {
        // Variables
        $oCliente = factory(Cliente::class)->make();
        $sUrl = $this->base_url . '/' . $oCliente->uuid;

        // Prueba seguridad
        $this->json('GET', $sUrl)->assertStatus(401);

        // Prueba la validacion
        $this->withoutMiddleware()->actingAs($this->user)->json('GET', $this->base_url . '/X')
            ->assertStatus(400)
            ->assertJson(["status" => "fail", "data" => ["errors" => true]]);

        // Prueba recurso no encontrado
        $this->mCliente
            ->shouldReceive('where')->once()->with('comercio_uuid', '=', $this->user->comercio_uuid)->andReturnSelf()
            ->shouldReceive('find')->once()->with($oCliente->uuid)->andReturnNull();
        $this->withoutMiddleware()->actingAs($this->user)->json('GET', $sUrl)
            ->assertStatus(404)
            ->assertJson(['status' => 'fail', "error" => true]);

        // Prueba el camino exitoso
        $this->mCliente
            ->shouldReceive('where')->once()->with('comercio_uuid', '=', $this->user->comercio_uuid)->andReturnSelf()
            ->shouldReceive('find')->once()->with($oCliente->uuid)->andReturn($oCliente);
        $this->withoutMiddleware()->actingAs($this->user)->json('GET', $sUrl)
            ->assertStatus(200)
            ->assertJson(['status' => "success", 'data' => ['cliente' => true]]);

        // Prueba exception
        $this->mCliente
            ->shouldReceive('where')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
        $this->withoutMiddleware()->json('GET', $sUrl)
            ->assertStatus(500)
            ->assertJson(['status' => 'error', "error" => true]);
    }
//
//    public function test_store()
//    {
//        // Variables
//        $oCliente = factory(Cliente::class)->make();
//        $aRequest = [
//            "email" => $oCliente->email,
//        ];
//
//        // Prueba seguridad
//        $this->json('POST', $this->base_url)->assertStatus(401);
//
//        // Prueba validacion
//        $this->withoutMiddleware()->actingAs($this->user)->json('POST', $this->base_url, [])
//            ->assertStatus(400)
//            ->assertJson(["status" => "fail", "data" => ["errors" => true]]);
//
//        // Prueba el camino exitoso
//        $this->mCliente->shouldReceive('create')->once()->andReturn($oCliente);
//        $this->withoutMiddleware()->actingAs($this->user)->json('POST', $this->base_url, $aRequest)
//            ->assertStatus(200)
//            ->assertJson(['status' => "success", 'data' => ['cliente' => true]]);
//
//        // Prueba exception
//        $this->mCliente->shouldReceive('create')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
//        $this->withoutMiddleware()->actingAs($this->user)->json('POST', $this->base_url, $aRequest)
//            ->assertStatus(500)
//            ->assertJson(['status' => 'error', "error" => true]);
//    }
//
//    public function test_update()
//    {
//        // Variables
//        $oCliente = factory(Cliente::class)->make();
//        $aRequest = [
//            "apellido_paterno" => $oCliente->apellido_paterno,
//            "apellido_materno" => $oCliente->apellido_materno,
//            "email" => $oCliente->email,
//            "estado" => $oCliente->estado,
//            "telefono" => $oCliente->telefono,
//            "direccion" => $oCliente->direccion,
//        ];
//        $sUrl = $this->base_url . '/' . $oCliente->uuid;
//
//        // Prueba seguridad
//        $this->json('PUT', $sUrl)->assertStatus(401);
//
//        // Prueba validacion
//        $this->withoutMiddleware()->json('PUT', $sUrl, [])
//        ->assertStatus(400)->assertJson(["status" => "fail", "data" => ["errors" => true]]);
//
//        // Prueba recurso no encontrado
//        $this->mCliente->shouldReceive('where')->once()->with('uuid', '=', $oCliente->uuid)
//        ->andReturnSelf()->shouldReceive('first')->once()->andReturnNull();
//        $this->withoutMiddleware()->json('PUT', $sUrl, $aRequest)
//        ->assertStatus(404)->assertJson(['status' => 'fail', "error" => true]);
//
//        // Prueba el camino exitoso
//        $this->mCliente->shouldReceive('where')->once()->with('uuid', '=', $oCliente->uuid)
//        ->andReturnSelf()->shouldReceive('first')->once()->andReturn($oCliente);
//        $this->withoutMiddleware()->json('PUT', $sUrl, $aRequest)
//        ->assertStatus(200)->assertJson(['status' => "success", 'data' => ['cliente' => true]]);
//
//        // Prueba exception
//        $this->mCliente->shouldReceive('where')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
//        $this->withoutMiddleware()->json('PUT', $sUrl, $aRequest)
//        ->assertStatus(500)->assertJson(['status' => 'error', "error" => true]);
//    }
//
//    public function test_destroy()
//    {
//        // Variables
//        $oCliente = factory(Cliente::class)->make();
//        $sUrl = $this->base_url . '/' . $oCliente->uuid;
//
//        // Prueba seguridad
//        $response = $this->json('DELETE', $sUrl);
//        $response->assertStatus(401);
//
//        // Prueba la validacion
//        $this->withoutMiddleware()->json('DELETE', $sUrl . 'X')
//        ->assertStatus(400)->assertJson(["status" => "fail", "data" => ["errors" => true]]);
//
//        // Prueba recurso no encontrado
//        $this->mCliente->shouldReceive('where')->once()->with('uuid', '=', $oCliente->uuid)
//        ->andReturnSelf()->shouldReceive('first')->once()->andReturnNull();
//        $this->withoutMiddleware()->json('DELETE', $sUrl)
//        ->assertStatus(404)->assertJson(['status' => 'fail', "error" => true]);
//
//        // Prueba camino exitoso
//        $this->mCliente->shouldReceive('where')->once()->with('uuid', '=', $oCliente->uuid)
//        ->andReturnSelf()->shouldReceive('first')->once()->andReturn($oCliente);
//        $this->withoutMiddleware()->json('DELETE', $sUrl)->assertStatus(204);
//
//        // Prueba exception
//        $this->mCliente->shouldReceive('where')->once()->with('uuid', '=', $oCliente->uuid)
//        ->andReturnSelf()->shouldReceive('first')->once()
//        ->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
//        $this->withoutMiddleware()->json('DELETE', $sUrl)
//        ->assertStatus(500)->assertJson(['status' => 'error', "error" => true]);
//    }
//
//    public function test_create()
//    {
//        $this->withoutMiddleware()->json('GET', $this->base_url . "/create")
//        ->assertStatus(200)->assertJson([]);
//    }
//
//    public function test_edit()
//    {
//        $this->withoutMiddleware()->json('GET', $this->base_url . "/999999/edit")
//        ->assertStatus(200)->assertJson([]);
//    }

}