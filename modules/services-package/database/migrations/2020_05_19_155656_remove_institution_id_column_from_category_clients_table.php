<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveInstitutionIdColumnFromCategoryClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_clients', function (Blueprint $table) {
            $table->dropForeign(['institutions_id']);
            $table->dropColumn('institutions_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_clients', function (Blueprint $table) {
            //
        });
    }
}
