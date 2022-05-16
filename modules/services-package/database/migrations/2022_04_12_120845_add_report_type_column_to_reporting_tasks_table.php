<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportTypeColumnToReportingTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reporting_tasks', function (Blueprint $table) {
            $table->string("reporting_type")
                ->nullable()
                ->after("institution_targeted_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reporting_tasks', function (Blueprint $table) {
            //
        });
    }
}
