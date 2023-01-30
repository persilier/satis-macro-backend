<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatments', function (Blueprint $table) {

            $table->uuid("transferred_to_targeted_institution_by")->nullable()->after("claim_id");
            $table->uuid("transferred_to_unit_by")->nullable()->after("transferred_to_targeted_institution_at");
            $table->uuid("validated_by")->nullable()->after("comments");

            $table->foreign("transferred_to_targeted_institution_by")
                ->references('id')
                ->on('staff')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign("transferred_to_unit_by")
                ->references('id')
                ->on('staff')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign("validated_by")
                ->references('id')
                ->on('staff')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treatments', function (Blueprint $table) {
            //
        });
    }
}

