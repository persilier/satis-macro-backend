<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdColumnToUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->uuid('institution_id')->nullable()->change();
            $table->uuid('parent_id')->nullable()->after('lead_id');
            $table->foreign('parent_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->uuid('institution_id')->change();
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
}
