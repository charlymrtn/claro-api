<?php

namespace Tests\Unit\App\Http\Controllers\API\v1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Medios\Tarjeta;
use App\Http\Controllers\API\v1\TarjetaController;

class TarjetaControllerTest extends TestCase
{

    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->mTarjeta = Mockery::mock(Eloquent::class,Tarjeta::class);
        $this->app->instance(Tarjeta::class, $this->mTarjeta);
        $this->base_url = '/v1/tarjeta/';
    }

    public function tearDown()
    {
        // Quita los mocks al finalizar
        Mockery::close();
    }

    public function test_index()
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZWYzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiOjE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRtaW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyALyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLRYk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_80XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc';
        $headers = [
            'Accept' => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];

        // Prueba seguridad
        $response = $this->json('GET', $this->base_url);
        $response->assertStatus(401);

        $this->mTarjeta->shouldReceive('withTrashed')->andReturnSelf()
            ->shouldReceive('where')
            ->with(Mockery::on(function ($where){
                $mockDb = Mockery::mock('Illuminate\Database\DatabaseManager');
                $subQuery = $mockDb
                    ->shouldReceive('orWhere')->with('uuid', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('nombre', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('marca', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('comercio_uuid', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('cliente_uuid', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('iin', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('pan', 'like', "%algo%")->andReturnSelf()
                    ->shouldReceive('orWhere')->with('terminacion', 'like', "%algo%")->andReturnSelf()
                    ->getMock();
                $where($subQuery);
                return true;
            }))->andReturnSelf()
            ->shouldReceive('orderBy')->once()->andReturnSelf()
            ->shouldReceive('paginate')->withArgs([25])->andReturnSelf()
            ->shouldReceive('jsonSerialize')->andReturnSelf();

        $response = $this->withMiddleware()->get( $this->base_url, $headers);
        $response->assertStatus(200)->assertJson(["status" =>"success", "data" => []]);
    }

    public function test_create()
    {
        $var = new TarjetaController(new Tarjeta);
        $vacio = $var->create();
        $this->assertEquals( null,$vacio);
    }

    public function test_store()
    {
        // Variables
        $oTarjeta = factory(Tarjeta::class)->make();
        $aParams = $oTarjeta->toArray();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZWYzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiOjE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRtaW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyALyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLRYk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_80XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc';
        $headers = [
            'Accept' => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];

        // Prueba seguridad
        $response = $this->json('POST', $this->base_url);
        $response->assertStatus(401);

        // Prueba validación de inputs
        $response= $this->withoutMiddleware()->post( $this->base_url, ['nombre' => 'XX'],$headers);
        $response->assertStatus(400);

        // Prueba de camino exitoso
        $this->mTarjeta->shouldReceive('create')->once()->andReturnSelf()
            ->shouldReceive('jsonSerialize')->andReturnSelf();
        $response = $this->post($this->base_url, $aParams, $headers);
        $response->assertStatus(200);

        // Prueva de exepcion
        $this->mTarjeta->shouldReceive('create')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.')
            ->shouldReceive('jsonSerialize')->andReturnSelf();
        $response = $this->post($this->base_url, $aParams, $headers);
        $response->assertStatus(400);
    }

    public function test_show()
    {
        // Variables
        $oTarjeta = factory(Tarjeta::class)->make();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZWYzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiOjE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRtaW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyALyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLRYk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_80XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc';
        $headers = [
            'Accept' => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];

        // Prueba seguridad
        $response = $this->json('GET', $this->base_url,['_token' => 'test']);
        $response->assertStatus(401);

        // Prueba validación de inputs
        $response = $this->withoutMiddleware()->call('GET', $this->base_url . 'X', $headers);
        $response->assertStatus(400);

        // Prueba registro no encontrado
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturn(null);
        $response = $this->withoutMiddleware()->call('GET', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(404);

        // Prueba camino exitoso
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturnSelf()->shouldReceive('jsonSerialize')->once();
        $response = $this->withoutMiddleware()->call('GET', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(200);

        // Prueba de exepcion
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andThrow('\Exception', 'Excepción generada por prueba unitaria.')
            ->shouldReceive('jsonSerialize');
        $response = $this->withoutMiddleware()->call('GET', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(500);
    }

    public function test_edit()
    {
        $var = new TarjetaController(new Tarjeta);
        $vacio = $var->edit(1);
        $this->assertEquals( null,$vacio);
    }

    public function test_update()
    {
        // Variables
        $oTarjeta = factory(Tarjeta::class)->make();
        $aParams = $oTarjeta->toArray();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZWYzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiOjE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRtaW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyALyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLRYk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_80XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc';
        $headers = [
            'Accept' => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];

        // Prueba seguridad
        $response = $this->json('PUT', $this->base_url.'XX');
        $response->assertStatus(401);

        // Prueba validación
        $response = $this->withoutMiddleware()->call('PUT', $this->base_url.''.$oTarjeta->uuid, [], $headers);
        $response->assertStatus(400);

        // Prueba registro no encontrado
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturn(null);
        $response = $this->withoutMiddleware()->call('PUT', $this->base_url.''.$oTarjeta->uuid, $aParams, $headers);
        $response->assertStatus(404);

        // Prueba camino exitoso
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturnSelf()
            ->shouldReceive('update')->once()->andReturn(true)->shouldReceive('jsonSerialize')->once();
        $response = $this->withoutMiddleware()->call('PUT', $this->base_url.''.$oTarjeta->uuid, $aParams, $headers);
        $response->assertStatus(200);

        // Prueba de exepcion
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturnSelf()
            ->shouldReceive('update')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
        $response = $this->withoutMiddleware()->call('PUT', $this->base_url.''.$oTarjeta->uuid, $aParams, $headers);
        $response->assertStatus(500);
    }

    public function test_destroy()
    {
        // Variables
        $oTarjeta = factory(Tarjeta::class)->make();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjU4MDQyOTFiYzdiZmVmMzVjZDg1NTQ4Yzk4OTE1MDJlYzlmOTAwODQ0ZjJhYmE1OWZkMDc0NzUyZjA1ODViYzM3NzlkZDE5MTI0ZjdiODI2In0.eyJhdWQiOiIxIiwianRpIjoiNTgwNDI5MWJjN2JmZWYzNWNkODU1NDhjOTg5MTUwMmVjOWY5MDA4NDRmMmFiYTU5ZmQwNzQ3NTJmMDU4NWJjMzc3OWRkMTkxMjRmN2I4MjYiLCJpYXQiOjE1MTU4MDk0MDksIm5iZiI6MTUxNTgwOTQwOSwiZXhwIjoxNTQ3MzQ1NDA5LCJzdWIiOiIxIiwic2NvcGVzIjpbInN1cGVyYWRtaW4iXX0.FnDV4lZcnJcmZ4-t9JbqOcLewuzWlfw9FMEgiXIaOS1BjG8xJubU9woBsWPxz-cv0egmpXUsHBnM394RPWyEpVni5R3AeOdSuuYVb0IcLojk9-Gz40M9UcrVSkBhCIHtUxxJo6pE4K4zF1FNSQpqcvw3rM9Ok1s0nCiVHtok4H3V7gA58vE9ihYYRpKks0CCMYjoQ9H_RlT46sujCK8zq-aSlj4bfbCYMFdZo0ptGU3kXWF3xYOe9l1-Ls3odxq40VJAj0Y97wk40-Ff2bTFmTO99Os3SAJyALyIFVAIKpQVUA3yumh6EGdZncs3lUO5kURnEuRtjaTtqcYwUkGvgGv9hP4xAfskAUmc_LMPjwmR93tmmYhCT9v6E-Tz8ZGdHzNW6Vu_fqRrSsFF7kUDPdKKbDHGHy6QdtFj5oma1Q2sKTbDd_sYyFquQ8ZxuR8NdoJRuiHT1DhohA-l2-exBRfMATScGU3ZXuyqcLRYk69fDwW5UtCSrMcQIkBwEo6qWnahPMO-_ojxvNZrNfM7PPvQ1fCIE2d8V9uMIA1jNFCKpVpekoXStxcC_hrD3MeyIMdU06lH_80XTv-n7Sj4NZw2uUtSRm4v2YKfScsfZ5fGkNmGJHdDZC00qFd4j4c38U_aGo4xX0kK1jjOO6xqu-WYpXJ_UTMo904AX43W6Kc';
        $headers = [
            'Accept' => 'application/json',
            'AUTHORIZATION' => 'Bearer ' . $token
        ];

        // Prueba seguridad
        $response = $this->json('DELETE', $this->base_url.'XX');
        $response->assertStatus(401);

        // Prueba validación
        $response = $this->withoutMiddleware()->call('DELETE', $this->base_url.'XX', $headers);
        $response->assertStatus(400);

        // Prueba registro no encontrado
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturn(null);
        $response = $this->withoutMiddleware()->call('DELETE', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(404);

        // Prueba camino exitoso
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturnSelf()
            ->shouldReceive('delete')->once()->andReturn(true)->shouldReceive('jsonSerialize');
        $response = $this->withoutMiddleware()->call('DELETE', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(200);

        // Prueba de exepcion
        $this->mTarjeta->shouldReceive('find')->once()->withArgs([$oTarjeta->uuid])->andReturnSelf()
            ->shouldReceive('delete')->once()->andThrow('\Exception', 'Excepción generada por prueba unitaria.');
        $response = $this->withoutMiddleware()->call('DELETE', $this->base_url.''.$oTarjeta->uuid, $headers);
        $response->assertStatus(500);
    }
}
