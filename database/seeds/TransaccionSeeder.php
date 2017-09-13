<?php

use Illuminate\Database\Seeder;
use App\Models\Transaccion;

class TransaccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Inserta valores iniciales
        Transaccion::create([
            'uuid' => '7e57d004-2b97-0e7a-b45f-5387367791cd',
            'comercio' => 'claropagos',
            'pais_id' => 'MEX',
            'prueba' => true,
            'operacion' => 'pago',
            'estatus'=> 'completada',
            'moneda' => 'MXN',
            'monto' => '250.00',
            'forma_pago' => 'tarjeta',
            'datos_pago' => '{}',
            'datos_antifraude' => '{}',
            'comercio_orden_id' => '345',
            'datos_comercio' => '{}',
            'datos_claropagos' => '{}',
            'datos_procesador' => '{}',
            'datos_destino' => '{}'
        ]);
    }
}
