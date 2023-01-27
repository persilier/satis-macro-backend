<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Satis2020\ServicePackage\Consts\NotificationConsts;

class CreateNotificationProofsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("to");
            $table->uuid('institution_id')->index()->nullable();
            $table->string("channel")->default(NotificationConsts::EMAIL_CHANNEL)->index();
            $table->longText("message");
            $table->string("status")->default(NotificationConsts::SENT_SUCCESS)->index();
            $table->timestamp("sent_at");
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
        Schema::dropIfExists('notification_proofs');
    }
}
