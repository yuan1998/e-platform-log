<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHospitalInfoTableAddDaZhongField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hospital_infos', function (Blueprint $table) {
            $table->string('dz_origin_id')->nullable();
            $table->string('dz_url')->nullable();
            $table->boolean('dz_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hospital_infos', function (Blueprint $table) {
            $table->dropColumn('dz_origin_id');
            $table->dropColumn('dz_url');
            $table->dropColumn('dz_enable');
        });
    }
}
