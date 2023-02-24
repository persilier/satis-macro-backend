<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSatifactionResponsibleToTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->uuid('satisfaction_responsible_staff_id')->nullable();
            $table->foreign('satisfaction_responsible_staff_id')->references('id')->on('staff');

            $table->uuid('satisfaction_responsible_unit_id')->nullable();
            $table->foreign('satisfaction_responsible_unit_id')->references('id')->on('units');

            $table->uuid('transfered_to_satisfaction_staff_by')->nullable();
            $table->foreign('transfered_to_satisfaction_staff_by')->references('id')->on('staff');

            $table->uuid('transfered_to_satisfaction_staff_by_unit')->nullable();
            $table->foreign('transfered_to_satisfaction_staff_by_unit')->references('id')->on('units');

            $table->timestamp('transfered_to_satisfaction_responsible_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treatments', function (Blueprint $table) {
            //
        });
    }
}
