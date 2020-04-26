<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIdentitesClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identites', function (Blueprint $table) {
            $table->json('id_card')->nullable()->after('ville');
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('id_card');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::drop('identites');
    }
}