<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('cliente', function (Blueprint $table) {
            // -------------------------------------------------------------------------
            $table->uuid('uuid')->primary();
            // -------------------------------------------------------------------------
            // Datos del comercio
            $table->uuid('comercio_uuid');
            $table->string('id_externo')->nullable();
            $table->timestamp('creacion_externa')->nullable();
            // Datos del cliente
            $table->string('nombre')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('sexo')->nullable();
            $table->string('email');
            $table->date('nacimiento')->nullable();
            $table->string('estado');
            // Objetos JSON
            $table->json('telefono')->nullable();
            $table->json('direccion')->nullable();
            // Traits
            $table->timestamps();
            $table->softDeletes();
            // -------------------------------------------------------------------------
            // Índices
            $table->index(['comercio_uuid']);
            $table->unique('comercio_uuid', 'email');
            $table->unique('comercio_uuid', 'id_externo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_sa')->dropIfExists('cliente');
    }
}
