<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuscripcionPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agrega columna
        Schema::connection('mysql_sa')->table('suscripcion_plan', function (Blueprint $table) {
            $table->string('id_externo');
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
        // Agrega columna
        Schema::connection('mysql_sa')->table('suscripcion_plan', function (Blueprint $table) {
            $table->dropIndex('comercio-id');
            $table->dropColumn('id_externo');
        });
    }
}
