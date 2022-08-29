<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHospitalInfoChangeFieldPlatformTypeNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hospital_infos', function (Blueprint $table) {
            $table->unsignedInteger('platform_type')->default(0)->nullable()->change();

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
            $table->unsignedInteger('platform_type')->nullable(false)->change();
        });
    }
}
