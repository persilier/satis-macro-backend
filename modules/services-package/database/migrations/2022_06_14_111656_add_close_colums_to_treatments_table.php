<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloseColumsToTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->longText("closed_reason")->nullable()->after("satisfaction_measured_by");
            $table->uuid("closed_by")
                ->index()
                ->nullable()
                ->after("closed_reason");
            $table->timestamp("closed_at")->nullable()->after("closed_reason");

            $table->foreign('closed_by')
                ->references('id')
                ->on('staff')
                ->onDelete("set null")
                ->onUpdate('cascade');
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
