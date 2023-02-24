<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToClaimObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_objects', function (Blueprint $table) {
            $table->string('time_unit')->nullable();
            $table->string('time_staff')->nullable();
            $table->string('time_treatment')->nullable();
            $table->string('time_validation')->nullable();
            $table->string('time_measure_satisfaction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_objects', function (Blueprint $table) {
            //
        });
    }
}
