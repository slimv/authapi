<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('group_permission')) {
            Schema::create('group_permission', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('scrub_id');
                $table->integer('group_id')->unsigned()->nullable();
                $table->integer('permission_id')->unsigned()->nullable();
                $table->timestamps();

                $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('group_permission');
    }
}
