<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstitutionIdColumnToTreatmentBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatment_boards', function (Blueprint $table) {
            $table->uuid('institution_id')->after('type');

            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treatment_boards', function (Blueprint $table) {
            //
        });
    }
}
