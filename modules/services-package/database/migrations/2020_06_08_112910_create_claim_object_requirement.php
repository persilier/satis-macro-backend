<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimObjectRequirement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_object_requirement', function (Blueprint $table) {
            $table->uuid('claim_object_id');
            $table->uuid('requirement_id');
            $table->timestamps();

            $table->foreign('claim_object_id')->references('id')->on('claim_objects');
            $table->foreign('requirement_id')->references('id')->on('requirements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_object_requirement');
    }
}
