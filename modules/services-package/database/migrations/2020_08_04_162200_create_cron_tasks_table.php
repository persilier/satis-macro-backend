<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cron_tasks', function (Blueprint $table) {
            $table->uuid('cron_id')->primary();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cron_tasks');
    }
}
