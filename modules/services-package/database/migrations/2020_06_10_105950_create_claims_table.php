<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->uuid('claim_object_id');
            $table->uuid('claimer_id');
            $table->uuid('relationship_id')->nullable();
            $table->uuid('account_targeted_id')->nullable();
            $table->uuid('institution_targeted_id');
            $table->uuid('unit_targeted_id')->nullable();
            $table->string('request_channel_slug');
            $table->string('response_channel_slug')->nullable();
            $table->timestamp('event_occured_at')->nullable();
            $table->text('claimer_expectation')->nullable();
            $table->integer('amount_disputed')->nullable();
            $table->string('amount_currency_slug')->nullable();
            $table->boolean('is_revival');
            $table->uuid('created_by');
            $table->uuid('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('active_treatment_id')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('claim_object_id')->references('id')->on('claim_objects');
            $table->foreign('claimer_id')->references('id')->on('identites');
            $table->foreign('relationship_id')->references('id')->on('relationships');
            $table->foreign('account_targeted_id')->references('id')->on('accounts');
            $table->foreign('institution_targeted_id')->references('id')->on('institutions');
            $table->foreign('unit_targeted_id')->references('id')->on('units');
            $table->foreign('created_by')->references('id')->on('staff');
            $table->foreign('completed_by')->references('id')->on('staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claims');
    }
}
