<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table){
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });

        Schema::table('accounts', function (Blueprint $table){
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });

        Schema::table('accounts', function (Blueprint $table){
            $table->uuid('client_institution_id');
            $table->foreign('client_institution_id')->references('id')->on('client_institution');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
