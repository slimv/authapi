<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActiveCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_active_codes')) {
            Schema::create('user_active_codes', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('scrub_id');
                $table->uuid('user_id')->index();
                $table->string('type', 50);                                         //forgot-password, signup
                $table->string('code', 250)->unique();
                $table->string('status', 50)->default('waiting-active');            //actived, waiting-active
                $table->dateTime('expire_at')->nullable();
                $table->timestamps();

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
        Schema::dropIfExists('user_active_codes');
    }
}
