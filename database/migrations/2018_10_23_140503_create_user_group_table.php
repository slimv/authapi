<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_group')) {
            Schema::create('user_group', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('scrub_id');
                $table->uuid('user_id')->index()->nullable();
                $table->integer('group_id')->unsigned()->nullable();
                $table->timestamps();

                $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('user_group');
    }
}
