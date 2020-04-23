<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('gender');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('ville');
            $table->string('id_card');
            $table->boolean('is_client')->default(false);
            $table->string('account_number')->nullable();
            $table->uuid('type_clients_id')->nullable();
            $table->uuid('category_clients_id')->nullable();
            $table->uuid('units_id')->nullable();
            $table->uuid('institutions_id');
            $table->json('others')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('type_clients_id')->references('id')->on('type_clients');
            $table->foreign('category_clients_id')->references('id')->on('category_clients');
            $table->foreign('units_id')->references('id')->on('units');
            $table->foreign('institutions_id')->references('id')->on('institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('clients');
    }
}