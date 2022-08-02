<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Satis2020\ServicePackage\Models\Revival;

class CreateRevivalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revivals', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->longText("message");
            $table->string("claim_status")->index();
            $table->uuid("institution_id")->index();
            $table->uuid("claim_id")->index();
            $table->uuid("created_by")->index();
            $table->uuid("staff_unit_id")->index();
            $table->uuid("targeted_staff_id")->index();
            $table->enum("status",[Revival::STATUS_AWAITING,Revival::STATUS_CONSIDERED])->default(Revival::STATUS_AWAITING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revivals');
    }
}
