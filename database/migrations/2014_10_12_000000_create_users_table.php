<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('users', function (Blueprint $table) {
            $table->increments('id'); // Usuario id en API únicamente
            $table->string('name'); // Nombre del usuario API
            $table->string('descripcion'); // Descripción del uso del usuario API
            $table->string('email')->unique();
            $table->string('password');
            $table->uuid('comercio_uuid')->unique(); // Comercio id
            $table->string('comercio_nombre'); // Nombre del usuario API
            $table->boolean('activo')->default(true);
            $table->json('config');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_sa')->dropIfExists('users');
    }
}
