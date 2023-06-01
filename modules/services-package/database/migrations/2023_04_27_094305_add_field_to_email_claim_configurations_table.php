<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToEmailClaimConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_claim_configurations', function (Blueprint $table) {
            
            $table->string('type')->nullable();
            $table->string('app_tenant')->nullable();
            $table->string('app_client_secret')->nullable();
            $table->string('app_client_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_claim_configurations', function (Blueprint $table) {
            //
        });
    }
}
