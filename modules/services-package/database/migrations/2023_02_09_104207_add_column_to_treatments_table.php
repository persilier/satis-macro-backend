<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->uuid('escalation_responsible_unit_id')->nullable();
            $table->foreign('escalation_responsible_unit_id')->references('id')->on('units');

            $table->uuid('escalation_responsible_staff_id')->nullable();
            $table->foreign('escalation_responsible_staff_id')->references('id')->on('staff');

            $table->uuid('escalation_satisfaction_measured_by')->nullable();
            $table->foreign('escalation_satisfaction_measured_by')->references('id')->on('staff');

            $table->text('escalation_solution_communicated')->nullable();
            $table->timestamp('escalation_satisfaction_measured_at')->nullable();
            $table->boolean('is_claimer_satisfied_after_escalation')->nullable();
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
