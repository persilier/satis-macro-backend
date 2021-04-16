<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportingTaskStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reporting_task_staff', function (Blueprint $table) {
            $table->uuid('reporting_task_id');
            $table->uuid('staff_id');
            $table->timestamps();

            $table->foreign('reporting_task_id')->references('id')->on('reporting_tasks');
            $table->foreign('staff_id')->references('id')->on('staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reporting_task_staff');
    }
}
