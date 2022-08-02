<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reporting_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('period');
            $table->uuid('institution_id');
            $table->uuid('institution_targeted_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('institution_id')->references('id')->on('institutions');
            $table->foreign('institution_targeted_id')->references('id')->on('institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reporting_tasks');
    }
}
