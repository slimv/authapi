<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_devices')) {
            Schema::create('user_devices', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('scrub_id');
                $table->uuid('user_id')->index();
                $table->string('device_type', 100);                                 //ios, android, web-browser
                $table->string('device_name', 250)->nullable();                     //fullname of device (for example iphone 6s plus)
                $table->string('device_id', 250);                                   //id which can be used to send notification
                $table->string('status', 50)->default('actived');                   //actived, deactived (if deactived wont get notification)
                $table->dateTime('last_access_at')->nullable();
                $table->timestamps();

                $table->unique(['device_id', 'user_id']);

                $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
