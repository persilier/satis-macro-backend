<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Satis2020\ServicePackage\Models\InactivityReactivationHistory;

class AddActionColumnToInactivityReactivationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inactivity_reactivation_histories', function (Blueprint $table) {
            $table->string("action")->default(InactivityReactivationHistory::ACTIVATION)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inactivity_reactivation_histories', function (Blueprint $table) {
            //
        });
    }
}
