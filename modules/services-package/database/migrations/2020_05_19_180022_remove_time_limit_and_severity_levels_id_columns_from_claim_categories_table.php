<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTimeLimitAndSeverityLevelsIdColumnsFromClaimCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_categories', function (Blueprint $table) {

            $table->dropColumn('time_limit');

            $table->dropColumn('severity_levels_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_categories', function (Blueprint $table) {
            //
        });
    }
}
