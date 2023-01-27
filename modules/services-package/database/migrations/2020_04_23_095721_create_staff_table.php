<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('identite_id');
            $table->uuid('position_id');
            $table->uuid('unit_id');
            $table->json('others')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('identite_id')->references('id')->on('identites');
            $table->foreign('position_id')->references('id')->on('positions');
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
        Schema::dropIfExists('staff');
    }
}
