<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('scrub_id');
                $table->uuid('client_id')->index()->nullable();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->string('key', 250);
                $table->string('name', 250);
                $table->longText('description');
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['client_id', 'key']);

                $table->foreign('parent_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('client_id')->references('id')->on('oauth_clients')->onUpdate('cascade')->onDelete('restrict');
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
        Schema::dropIfExists('groups');
    }
}
