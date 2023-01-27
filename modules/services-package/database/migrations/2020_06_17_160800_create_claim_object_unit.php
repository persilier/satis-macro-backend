<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimObjectUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_object_unit', function (Blueprint $table) {
            $table->uuid('claim_object_id');
            $table->uuid('unit_id');
            $table->timestamps();

            $table->foreign('claim_object_id')->references('id')->on('claim_objects');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_object_unit');
    }
}
