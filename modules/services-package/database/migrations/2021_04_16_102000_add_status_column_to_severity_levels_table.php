<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddNoteColumnToActiveTreatmentTable
 */
class AddStatusColumnToSeverityLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('severity_levels', function (Blueprint $table) {
            $table->enum('status', ['low', 'medium', 'high'])->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('severity_levels', function (Blueprint $table) {
            //
        });
    }
}
