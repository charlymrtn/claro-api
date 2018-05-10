<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransaccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('transaccion', function (Blueprint $table) {
            // Identificador
            $table->uuid('uuid');
            $table->primary('uuid');
            //Catalogos
            $table->uuid('comercio_uuid');
            $table->enum('estatus', [
                'pendiente',
                'aprobada-antifraude', 'rechazada-antifraude',
                'completada', 'reembolsada', 'reembolso-parcial', 'autorizada',
                'cancelada', 'rechazada-banco', 'contracargo-pendiente', 'contracargo-rechazado',
                'contracargada', 'fallida', 'declinada'
            ]);
            $table->string('pais', 3);
            $table->string('moneda', 3);
            // Datos de transaccion
            $table->boolean('prueba');
            $table->decimal('monto',19, 4);
            $table->enum('operacion', ['pago', 'preautorizacion', 'autorizacion', 'cancelacion']);
            $table->enum('forma_pago', ['tarjeta', 'telmex-recibo', 'telcel-recibo', 'paypal', 'applepay', 'androidpay', 'visa-checkout', 'masterpass', 'mercadopago']);
            $table->json('datos_pago');
            $table->json('datos_antifraude');
            $table->json('datos_comercio');
            $table->json('datos_claropagos');
            $table->json('datos_procesador');
            $table->json('datos_destino');
                //Traits
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_sa')->dropIfExists('transaccion');
    }
}
