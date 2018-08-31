<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuscripcionPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('suscripcion_plan', function (Blueprint $table) {
            // -------------------------------------------------------------------------
            $table->uuid('uuid')->primary();
            // -------------------------------------------------------------------------
            // Datos del comercio
            $table->uuid('comercio_uuid');
            $table->string('id_externo');
            // Datos del plan
            $table->string('nombre');
            $table->decimal('monto',19, 4);
            $table->integer('frecuencia');
            $table->string('tipo_periodo');
            $table->integer('max_reintentos');
            $table->integer('prueba_frecuencia');
            $table->string('prueba_tipo_periodo');
            $table->string('estado');
            $table->boolean('puede_suscribir');
            $table->string('moneda_iso_a3', 3);
            // Traits
            $table->timestamps();
            $table->softDeletes();
            // -------------------------------------------------------------------------
            // Índices adicionales
            $table->index('comercio_uuid');
            $table->unique(['comercio_uuid', 'id_externo'], 'comercio-id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suscripcion_plan');
    }
}
