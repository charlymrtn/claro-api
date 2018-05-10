<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComercioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sa')->create('comercio', function (Blueprint $table) {
            // -------------------------------------------------------------------------
            $table->uuid('uuid')->unique();
            // -------------------------------------------------------------------------
            // Config
            $table->json('config')->nullable();
            // Traits
            $table->timestamps();
            $table->softDeletes();
            // -------------------------------------------------------------------------
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_sa')->drop('comercio');
    }
}
