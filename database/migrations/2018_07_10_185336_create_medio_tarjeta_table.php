<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedioTarjetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('medio_tarjeta', function (Blueprint $table) {
            // -------------------------------------------------------------------------
            $table->uuid('uuid')->primary();
            // -------------------------------------------------------------------------
            // Datos del comercio
            $table->uuid('comercio_uuid');
            $table->uuid('cliente_uuid')->nullable();
            $table->string('iin');
            $table->string('marca');
            $table->string('pan');
            $table->string('terminacion');
            $table->string('nombre')->nullable();
            $table->string('expiracion_mes', 2);
            $table->string('expiracion_anio', 4);
            $table->string('inicio_mes', 2)->nullable();
            $table->string('inicio_anio', 4)->nullable();
            // Otros datos
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('pan_hash');
            $table->string('token')->nullable();
            $table->boolean('default')->default(false);
            $table->boolean('cargo_unico')->default(true);
            // Objetos JSON
            $table->json('direccion')->nullable();
            // Traits
            $table->timestamps();
            $table->softDeletes();
            // -------------------------------------------------------------------------
            // Índices
            $table->index(['comercio_uuid', 'cliente_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medio_tarjeta');
    }
}
