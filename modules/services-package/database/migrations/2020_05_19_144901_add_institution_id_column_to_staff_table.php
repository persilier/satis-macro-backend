<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstitutionIdColumnToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->uuid('unit_id')->nullable()->change();
            $table->uuid('institution_id')->nullable()->after('unit_id');
            $table->foreign('institution_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->uuid('unit_id')->change();
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });
    }
}
