<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table){
            $table->dropForeign(['category_clients_id']);
            $table->dropColumn('category_clients_id');
        });

        Schema::table('clients', function (Blueprint $table){
            $table->dropForeign(['type_clients_id']);
            $table->dropColumn('type_clients_id');
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
