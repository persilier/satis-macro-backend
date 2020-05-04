<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClaimObjectsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_categories', function (Blueprint $table) {
            $table->tinyInteger('time_limit')->nullable();
            $table->uuid('severity_level_id')->nullable();
            $table->foreign('severity_level_id')->references('id')->on('severity_level');
        });

        Schema::table('claim_objects', function (Blueprint $table) {
            $table->tinyInteger('time_limit')->nullable();
            $table->uuid('severity_level_id')->nullable();
            $table->foreign('severity_level_id')->references('id')->on('severity_level');
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