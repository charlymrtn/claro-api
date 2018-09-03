<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Suscripciones\Plan;

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
        if (!Schema::hasColumn('suscripcion_plan', 'id_externo')) {
            Schema::connection('mysql_sa')->table('suscripcion_plan', function (Blueprint $table) {
                $table->string('id_externo');
            });
        }
        // Modifica registros existentes
        $cPlanes = Plan::all();
        foreach ($cPlanes as $oPlan) {
            if(empty($oPlan->id_externo)) {
                $oPlan->id_externo = str_random(6);
                $oPlan->save();
            }
        }
        // Agrega índice
        Schema::connection('mysql_sa')->table('suscripcion_plan', function (Blueprint $table) {
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
        // Borra columna e índice
        Schema::connection('mysql_sa')->table('suscripcion_plan', function (Blueprint $table) {
            $table->dropIndex('comercio-id');
            $table->dropColumn('id_externo');
        });
    }
}
