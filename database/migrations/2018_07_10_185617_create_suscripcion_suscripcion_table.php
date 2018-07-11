<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuscripcionSuscripcionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('suscripcion_suscripcion', function (Blueprint $table) {
            // -------------------------------------------------------------------------
            $table->uuid('uuid')->primary();
            // -------------------------------------------------------------------------
            // Datos del comercio
            $table->uuid('comercio_uuid');
            $table->uuid('plan_uuid');
            $table->uuid('cliente_uuid');
            $table->string('metodo_pago');
            $table->uuid('metodo_pago_uuid');
            // Datos de la suscripción
            $table->string('estado');
            $table->date('inicio')->nullable();
            $table->date('fin')->nullable();
            $table->date('prueba_inicio')->nullable();
            $table->date('prueba_fin')->nullable();
            $table->date('periodo_fecha_inicio')->nullable();
            $table->date('periodo_fecha_fin')->nullable();
            $table->date('fecha_proximo_cargo')->nullable();
            // Traits
            $table->timestamps();
            $table->softDeletes();
            // -------------------------------------------------------------------------
            // Índices adicionales
            $table->index(['comercio_uuid', 'plan_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suscripcion_suscripcion');
    }
}
