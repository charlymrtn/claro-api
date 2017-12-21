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
        Schema::connection('mysql_sa')->create('transaccions', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('comercio');
            $table->string('pais_id', 3);
            $table->boolean('prueba');
            $table->enum('operacion', ['pago', 'preautorizacion', 'autorizacion', 'cancelacion']);
            $table->enum('estatus', ['pendiente', 'completada', 'reembolsada', 'reembolso-parcial', 'autorizada',
            'cancelada', 'rechazada-banco', 'rechazada-antifraude', 'contracargo-pendiente', 'contracargo-rechazado', '
            contracargada', 'fallida']);
            $table->string('moneda', 3);
            $table->decimal('monto',19, 4);
            $table->enum('forma_pago', ['tarjeta', 'telmex-recibo', 'telcel-recibo', 'paypal', 'applepay',
                'androidpay', 'visa-checkout', 'masterpass']);
            $table->json('datos_pago');
            $table->json('datos_antifraude');
            $table->integer('comercio_orden_id');
            $table->json('datos_comercio');
            $table->json('datos_claropagos');
            $table->json('datos_procesador');
            $table->json('datos_destino');
            $table->primary('uuid');
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
        Schema::connection('mysql_sa')->dropIfExists('transaccions');
    }
}
