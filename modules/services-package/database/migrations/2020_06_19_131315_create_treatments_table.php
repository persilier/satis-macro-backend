<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('claim_id');
            $table->timestamp('transferred_to_targeted_institution_at')->nullable();
            $table->timestamp('transferred_to_unit_at')->nullable();
            $table->uuid('responsible_unit_id')->nullable();
            $table->timestamp('assigned_to_staff_at')->nullable();
            $table->uuid('assigned_to_staff_by')->nullable();
            $table->uuid('responsible_staff_id')->nullable();
            $table->timestamp('declared_unfounded_at')->nullable();
            $table->longText('unfounded_reason')->nullable();
            $table->timestamp('solved_at')->nullable();
            $table->bigInteger('amount_returned')->nullable();
            $table->longText('solution')->nullable();
            $table->longText('preventive_measures')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('solution_communicated')->nullable();
            $table->timestamp('satisfaction_measured_at')->nullable();
            $table->boolean('is_claimer_satisfied')->nullable();
            $table->longText('unsatisfied_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('claim_id')->references('id')->on('claims');
            $table->foreign('responsible_unit_id')->references('id')->on('units');
            $table->foreign('assigned_to_staff_by')->references('id')->on('staff');
            $table->foreign('responsible_staff_id')->references('id')->on('staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treatments');
    }
}
