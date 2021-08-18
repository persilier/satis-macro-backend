<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnEmailTelephoneIdentitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identites', function (Blueprint $table) {
            $table->dropIndex('identites_telephone_unique');
            $table->dropIndex('identites_email_unique');
        });

        Schema::table('identites', function (Blueprint $table) {
            $table->json('email')->nullable()->change();
            $table->json('telephone')->nullable()->change();
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