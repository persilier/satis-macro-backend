<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Satis2020\Escalation\Models\TreatmentBoard;

class AddStatusColumnToTreatmentBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatment_boards', function (Blueprint $table) {
            $table->string('status')->default(TreatmentBoard::ACTIVE)->after('type');
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
