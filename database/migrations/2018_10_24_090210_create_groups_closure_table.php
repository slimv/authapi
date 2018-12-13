<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsClosureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('groups_closure')) {
            Schema::create('groups_closure', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('ancestor_id')->unsigned()->nullable();
                $table->integer('descendant_id')->unsigned()->nullable();
                $table->integer('depth')->unsigned()->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('groups_closure');
    }
}
