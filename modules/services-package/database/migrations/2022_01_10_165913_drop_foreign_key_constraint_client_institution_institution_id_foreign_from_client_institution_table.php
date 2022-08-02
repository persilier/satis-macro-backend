<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeyConstraintClientInstitutionInstitutionIdForeignFromClientInstitutionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_institution', function (Blueprint $table) {
            $table->dropForeign('client_institution_institution_id_foreign');
            $table->dropIndex('client_institution_institution_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_institution', function (Blueprint $table) {
            //
        });
    }
}
