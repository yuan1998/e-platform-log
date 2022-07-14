<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHospitalInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospital_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('origin_id');
            $table->unsignedInteger('platform_type');
            $table->boolean('enable');

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
        Schema::dropIfExists('hospital_infos');
    }
}
