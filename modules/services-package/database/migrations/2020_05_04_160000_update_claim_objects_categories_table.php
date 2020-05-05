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
            $table->tinyInteger('time_limit')->nullable()->after('name');
            $table->uuid('severity_levels_id')->nullable()->after('time_limit');
            $table->foreign('severity_levels_id')->references('id')->on('severity_levels');
        });

        Schema::table('claim_objects', function (Blueprint $table) {
            $table->tinyInteger('time_limit')->nullable()->after('name');
            $table->uuid('severity_levels_id')->nullable()->after('time_limit');
            $table->foreign('severity_levels_id')->references('id')->on('severity_levels');
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