<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('lastname');
            $table->dropColumn('firstname');
            $table->dropColumn('gender');
            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->dropColumn('ville');
            $table->dropColumn('is_client');
            $table->uuid('identites_id')->after('institutions_id');
            $table->foreign('identites_id')->references('id')->on('identites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::drop('clients');
    }
}