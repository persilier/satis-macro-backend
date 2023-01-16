<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReassignmentToPilotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reassignment_to_pilots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_pilot_id');
            $table->uuid('pilot_id');
            $table->uuid('claim_id');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reassignment_to_pilots');
    }
}
