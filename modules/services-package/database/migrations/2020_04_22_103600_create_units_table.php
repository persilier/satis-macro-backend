<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('units');

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('unit_type_id');
            $table->uuid('institution_id');
            $table->json('others')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_type_id')->references('id')->on('unit_types');
            $table->foreign('institution_id')->references('id')->on('institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
}
